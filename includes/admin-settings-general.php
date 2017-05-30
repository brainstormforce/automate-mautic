<?php	// Get list of current General entries.
			$entries = array();
			foreach ( $GLOBALS['menu'] as $entry ) {
				if ( false !== strpos( $entry[2], '.php' ) ) {
					$entries[ $entry[2] ] = $entry[0];
				}
			}

			// Remove <span> elements with notification bubbles (e.g. update or comment count).
			if ( isset( $entries['plugins.php'] ) ) {
				$entries['plugins.php'] = preg_replace( '/ <span.*span>/', '', $entries['plugins.php'] );
			}
			if ( isset( $entries['edit-comments.php'] ) ) {
				$entries['edit-comments.php'] = preg_replace( '/ <span.*span>/', '', $entries['edit-comments.php'] );
			}

			$entries['top'] = __( 'Top-Level (top)', 'convertplug-v2' );
			$entries['middle'] = __( 'Top-Level (middle)', 'convertplug-v2' );
			$entries['bottom'] = __( 'Top-Level (bottom)', 'convertplug-v2' );

			$select_box = '<select name="bsf_menu_position" >' . "\n";
			foreach ( $entries as $page => $entry ) {
				$select_box .= '<option '. selected( $page, $menu_position, false ) . ' value="' . $page . '">' . $entry . "</option>\n";
			}
			$select_box .= "</select>\n";
?>
		  <div class="cp-gen-set-content">
		    <h3 class="cp-gen-set-title"><?php _e("Advanced","convertplug-v2");?></h3>
		    <form method="post" class="cp-settings-form">
		         <?php
              if ( current_user_can( 'manage_options' ) ) {
            ?>
              <div class="debug-section cp-access-roles">
                  <table class="cp-postbox-table form-table">
                    <tr>
						<th scope="row"><label for="option-admin-menu-parent-page"><?php _e( 'Admin Menu Position ', 'convertplug-v2' ); ?></label></th>
						<td><?php echo $select_box; ?></td>
					</tr>
                    <tr>
                      <th scope="row">
                        <label for="cp-access-user-role"><strong><?php _e( "Allow ConvertPlug For", "smile" ); ?></strong>
                          <span class="cp-tooltip-icon has-tip" data-position="top" style="cursor: help;" title="<?php _e( "ConvertPlug dashboard access will be provided to selected user roles. By default, Administrator user role has complete access of ConvertPlug & it can not be changed.", "smile" ); ?>">
                            <i class="dashicons dashicons-editor-help"></i>
                          </span>
                        </label>
                      </th>
                      <td>
                        <ul class="checkbox-grid">
                          <?php
                          	// Get saved access roles
                            global $wp_roles;
                            $roles = $wp_roles->get_names();

                            unset($roles['administrator']);
							// if( ! $cp_access_roles ) {
							// 	$cp_access_roles = array();
							// }
							$cp_access_roles = array();
                         	foreach( $roles as $key => $role ) { 
                  			?>
                              <li>
                                <input type="checkbox" name="cp_access_role[]" <?php if( in_array( $key, $cp_access_roles ) ) { echo "checked='checked';";  } ?> value="<?php echo $key; ?>" />
                                  <?php echo $role; ?>
                              </li>
                          <?php } ?>
                        </ul>
                      </td>
                    </tr>
                  </table>
              </div>
            <?php } ?>
            <p class="submit">
            <input type="hidden" name="curr_tab" value="1">
            <input type="hidden" name="cp-update-settings-nonce" id="cp-update-settings-nonce" value="<?php echo wp_create_nonce( 'cp-update-settings-nonce' ); ?>" />
            <button type="submit" class="button-primary button button-large button-update-settings"><?php _e("Save Settings", "convertplug-v2"); ?></button>
            </p>
            </form>
		  </div> 