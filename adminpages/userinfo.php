<?php
	global $wpdb, $current_user;

	//only admins can get this
if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'pmpro_approvals' ) ) {
	die( 'You do not have permission to perform this action.' );
}

if ( isset( $_REQUEST['l'] ) ) {
	$l = intval( $_REQUEST['l'] );
} else {
	// Default to a random level that the user has. Hopefully we never actually do this.
	$levels = pmpro_getMembershipLevelsForUser( $current_user->ID );
	if ( ! empty( $levels ) ) {
		$l = $levels[0]->id;
	} else {
		$l = 0;
	}
}

if ( ! empty( $_REQUEST['approve'] ) ) {
	PMPro_Approvals::approveMember( intval( $_REQUEST['approve'] ), $l );
} elseif ( ! empty( $_REQUEST['deny'] ) ) {
	PMPro_Approvals::denyMember( intval( $_REQUEST['deny'] ), $l );
} elseif ( ! empty( $_REQUEST['unapprove'] ) ) {
	PMPro_Approvals::resetMember( intval( $_REQUEST['unapprove'] ), $l );
}

	//get the user
if ( empty( $_REQUEST['user_id'] ) ) {
	wp_die( __( 'No user id passed in.', 'pmpro-approvals' ) );
} else {
	$user = get_userdata( intval( $_REQUEST['user_id'] ) );

	//user found?
	if ( empty( $user->ID ) ) {
		wp_die( sprintf( __( 'No user found with ID %d.', 'pmpro-approvals' ), intval( $_REQUEST['user_id'] ) ) );
	}
}

// Fetch user's latest WooCommerce order address
$user_orders = wc_get_orders( array(
	'customer_id' => $user->ID,
	'limit' => 1,
	'orderby' => 'date',
	'order' => 'DESC',
) );

$address = 'Not Available';
$phone = 'Not Available';
if ( ! empty( $user_orders ) ) {
	$order = $user_orders[0];
	$address = $order->get_billing_address_1() . ', ' . $order->get_billing_city() . ', ' . $order->get_billing_state() . ', ' . $order->get_billing_postcode() . ', ' . $order->get_billing_country();
	$phone = $order->get_billing_phone();
}
?>
<div class="wrap pmpro_admin">	
	<form id="posts-filter" method="get" action="">	
	<h2>
		<?php echo intval( $user->ID ); ?> - <?php echo esc_html( $user->display_name ); ?> (<?php echo esc_html( $user->user_login ); ?>)
		<a href="<?php echo admin_url( 'user-edit.php?user_id=' . intval( $user->ID ) ); ?>" class="button button-primary"><?php esc_html_e( 'Edit Profile', 'pmpro-approvals' ); ?></a>
	</h2>	
	
	<h3><?php esc_html_e( 'Account Information', 'pmpro-approvals' ); ?></h3>
	<table class="form-table">
		<tr>
			<th><label><?php esc_html_e( 'User ID', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo intval( $user->ID ); ?></td>
		</tr>		
		<tr>
			<th><label><?php _e( 'Username', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $user->user_login ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Email', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $user->user_email ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Membership Level', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( pmpro_getMembershipLevelForUser( $user->ID )->name ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Approval Status', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( PMPro_Approvals::getUserApprovalStatus( $user->ID, $l ) ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'User Address', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $address ); ?></td>
		</tr>
		<tr>
			<th><label><?php _e( 'Phone Number', 'pmpro-approvals' ); ?></label></th>
			<td><?php echo esc_html( $phone ); ?></td>
		</tr>
	</table>
	</form>
</div>
