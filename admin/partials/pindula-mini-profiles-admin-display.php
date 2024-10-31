<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.controvert.co/
 * @since      1.0.0
 *
 * @package    Pindula_Mini_Profiles
 * @subpackage Pindula_Mini_Profiles/admin/partials
 */
?>

<div class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php esc_attr_e( '', 'wp_admin_style' ); ?></h1>

	<div id="poststuff">
	<?php if( isset($update_result) ): ?>
		<?php if( $update_result ): ?>
			<h2 class="hndle"><span><?php esc_attr_e(
										'Update Successful', 'wp_admin_style'
									); ?></span></h2>
		<?php else: ?>
			<h2 class="hndle"><span><?php esc_attr_e(
										'Update was unsuccessful', 'wp_admin_style'
									); ?></span></h2>
		<?php endif ?>	
	<?php endif ?>	
		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">

				<div class="meta-box-sortables ui-sortable">	
					<div class="postbox">
						<div class="handlediv" title="Click to toggle"><br></div>
						<!-- Toggle -->

						<h2 class="hndle">
						<form name="quote_pwiki_form_" method="post" action="" style="display:inline">
							<input type="hidden" name="quote_pwiki_form_submitted" value="U_P" />	
								<input class="button-primary" type="submit" name="quote_pwiki_form__submit" value="Update Profiles" />	
							</form>
						
						</h2>
						<!--div class="inside">
						</div>
						<!- .inside -->
					</div>
					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->
		</div>
		<!-- #post-body .metabox-holder .columns-2 -->
		<br class="clear">
	</div>
	<!-- #poststuff -->
</div> <!-- .wrap -->
