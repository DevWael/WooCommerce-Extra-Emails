<?php
defined( 'ABSPATH' ) || exit; //prevent direct file access.
if ( ! class_exists( 'EA_Options_Builder' ) ) {
	class EA_Options_Builder {

		private $inputs = array();
		private $form = array();
		private $menu = array();
		private $prefix = '';
		private $action = '';
		private $title = '';
		private $redirect = '';

		/**
		 * WSD_Form_Builder constructor.
		 *
		 * @param $args array of form build data
		 */
		public function __construct( $args ) {
			$this->title    = $args['title'];
			$this->action   = $args['action'];
			$this->redirect = $args['redirect_url'];
			$this->create_setting_page( $args['setting_page'] );
			$this->set_form_data( $args['form_args'], $args['input_args'], $args['prefix'] );
			$this->loadHooks();
		}

		public function loadHooks() {
			add_action( 'admin_menu', array( $this, 'menu_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'loadAssets' ) );
			add_action( 'admin_post_' . $this->action, array( $this, 'save' ) );
		}

		public function save() {
			//todo check for nonce and capability
			$inputs = $this->inputs;
			$prefix = $this->prefix;
			foreach ( $inputs as $input ) {
				if ( is_array( $_POST ) && array_key_exists( $input['id'], $_POST ) ) {
					if ( isset( $_POST[ $input['id'] ] ) ) {
						if ( is_array( $_POST[ $input['id'] ] ) ) {
							$data      = array();
							$post_data = $_POST[ $input['id'] ];
							foreach ( $post_data as $one_data ) {
								$data[] = sanitize_text_field( $one_data );//just sanitizing fields and regrouping again into the array before saving to db
							}
							update_option( $prefix . $input['id'], $data );
						} else {
							$post_data = $_POST[ $input['id'] ];
							switch ( $post_data ) {
								case filter_var( $post_data, FILTER_VALIDATE_URL ):
									$data = esc_url( $post_data );
									break;
								case is_email( $post_data ):
									$data = sanitize_email( $post_data );
									break;
								default:
									$data = sanitize_textarea_field( $post_data );
							}
							update_option( $prefix . $input['id'], $data );
						}
					}
				}

			}
			wp_safe_redirect( $this->redirect );
			exit();
		}

		public function loadAssets() {
			wp_enqueue_media();
			wp_enqueue_style( 'wp-color-picker' );
		}

		public function menu_page() {
			$menu_data = $this->menu;
			if ( $menu_data['parent'] ) {
				add_menu_page(
					$menu_data['name'],
					$menu_data['name'],
					$menu_data['capability'],
					$menu_data['slug'],
					array( $this, 'setting_page_content' ),
					$menu_data['icon'],
					$menu_data['position']
				);
			} else {
				add_submenu_page(
					$menu_data['parent_slug'],
					$menu_data['name'],
					$menu_data['name'],
					$menu_data['capability'],
					$menu_data['slug'],
					array( $this, 'setting_page_content' )
				);
			}
		}

		public function setting_page_content() {
			$this->css_codes();
			?>
			<div class="wrap">
				<h2><?php echo esc_html( $this->title ) ?></h2>
				<?php $this->create_form(); ?>
			</div>
			<?php
			$this->javaScript_codes();
		}

		/**
		 * adding fields style here
		 */
		private function css_codes() {
			?>
			<style>

			</style>
			<?php
		}

		/**
		 * adding scripts init codes here
		 */
		private function javaScript_codes() {
			?>
			<script>
                (function ($) {
                    $(function () {
                        $('.wsd-color-field').wpColorPicker();
                    });

                    let file_frame, currentElement;
                    $(document).on('click', '.wsd-browse', function (event) {
                        // console.log($(this));
                        currentElement = $(this);
                        event.preventDefault();
                        if (file_frame) {
                            file_frame.open();
                            return;
                        }
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: $(this).data('uploader_title'),
                            library: {
                                type: 'image',
                            },
                            button: {
                                text: $(this).data('uploader_button_text'),
                            },
                            multiple: false
                        });
                        file_frame.on('select', function () {
                            // console.log(currentElement.parent());
                            let attachment = file_frame.state().get('selection').first().toJSON();
                            //console.log(attachment);
                            currentElement.siblings('.wsd-image-preview').find('img').attr('src', attachment.url);
                            currentElement.siblings('.wsd-item-id').val(attachment.id);
                        });
                        file_frame.open();
                    });
                })(jQuery);
			</script>
			<?php
		}

		/**
		 * @param $form_data array data for building form
		 * @param $inputs_data array data for building inputs
		 * @param string $prefix string prefixing inputs
		 */
		private function set_form_data( $form_data, $inputs_data, $prefix = '' ) {
			$this->form   = $form_data;
			$this->inputs = $inputs_data;
			$this->prefix = $prefix;
		}

		private function create_setting_page( $args = array() ) {
			$this->menu = $args;
		}

		private function get_value( $args ) {
			return get_option( $this->prefix . $args['id'] );
		}

		private function get_field_description( $args ) {
			if ( ! empty( $args['desc'] ) ) {
				$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
			} else {
				$desc = '';
			}

			return $desc;
		}

		private function text_input( $args ) {
			$value = $this->get_value( $args );

			return '<input type="text" name="' . $args['id'] . '" id="wsd_input_text_' . $args['id'] . '" class="regular-text wsd_input_text_' . $args['class'] . '" placeholder="' .
			       ( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ) . '" value="' . esc_attr( $value ) . '">' . $this->get_field_description( $args );
		}

		private function number_input( $args ) {
			$value = $this->get_value( $args );

			//todo add class and id
			return sprintf( '<input type="number" name="%1$s" id="wsd_input_number_%1$s" min="%6$s" max="%7$s" class="regular-text wsd_input_number_%2$s" placeholder="%3$s" value="%4$s"> %5$s',
				esc_attr( $args['id'] ),
				esc_attr( $args['class'] ),
				( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ),
				esc_attr( $value ),
				$this->get_field_description( $args ),
				isset( $args['min'] ) ? esc_attr( $args['min'] ) : '',
				isset( $args['max'] ) ? esc_attr( $args['max'] ) : ''
			);
		}

		private function url_input( $args ) {
			$value = $this->get_value( $args );

			//todo add class and id
			return sprintf( '<input type="url" name="%1$s" id="wsd_input_url_%1$s" class="regular-text wsd_input_url_%2$s" placeholder="%3$s" value="%4$s"> %5$s',
				esc_attr( $args['id'] ),
				esc_attr( $args['class'] ),
				( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ),
				esc_attr( $value ),
				$this->get_field_description( $args )
			);
		}

		private function color_input( $args ) {
			$value = $this->get_value( $args );

			//todo add class and id
			return sprintf( '<input type="text" name="%1$s" id="wsd_input_color_%1$s" class="wsd-color-field wsd_input_color_%2$s" placeholder="%3$s" value="%4$s"> %5$s',
				esc_attr( $args['id'] ),
				esc_attr( $args['class'] ),
				( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ),
				esc_attr( $value ),
				$this->get_field_description( $args )
			);
		}

		private function upload_input( $args ) {
			$value = $this->get_value( $args );
			$label = isset( $args['button_label'] ) ? $args['button_label'] : esc_html__( 'Choose Image', 'wsd' );
			$html  = '<div class="wsd-media-uploader-container" id="wsd-media-uploader-container-' . $args['id'] . '">';
			$html  .= sprintf( '<input type="text" class="hide wsd-item-id" id="%1$s" name="%1$s" value="%2$s"/>', $args['id'], $value );
			$html  .= '<button type="button" class="button wsd-browse">' . $label . '</button>';
			$html  .= $this->get_field_description( $args );
			if ( $value && wp_attachment_is_image( $value ) ) {
				$html .= '<p class="wsd-image-preview"><img class="img-responsive" src="' . wp_get_attachment_image_url( $value ) . '"/></p>';
			} else {
				$html .= '<p class="wsd-image-preview"><img class="img-responsive" src=""/></p>';
			}
			$html .= '</div>';

			return $html;
		}

		private function textarea_input( $args ) {
			$value = $this->get_value( $args );

			//todo add class and id
			return sprintf( '<textarea rows="5" cols="55" name="%1$s" id="wsd_input_textarea_%1$s" class="regular-text wsd_input_textarea_%2$s" placeholder="%3$s">%4$s</textarea> %5$s',
				esc_attr( $args['id'] ),
				esc_attr( $args['class'] ),
				( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ),
				esc_html( $value ),
				$this->get_field_description( $args )
			);
		}

		private function email_input( $args ) {
			$value = $this->get_value( $args );

			return '<input type="email" name="' . $args['id'] . '" id="wsd_input_text_' . $args['id'] . '" class="regular-text wsd_input_text_' . $args['class'] . '" placeholder="'
			       . ( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ) . '" value="' . esc_attr( $value ) . '">' . $this->get_field_description( $args );
		}

		private function password_input( $args ) {
			$value = $this->get_value( $args );

			return '<input type="password" name="' . $args['id'] . '" id="wsd_input_text_' . $args['id'] . '" class="regular-text wsd_input_text_' . $args['class']
			       . '" placeholder="' . ( isset( $args['placeholder'] ) ? $args['placeholder'] : '' ) . '">' . $this->get_field_description( $args );
		}

		private function select_input( $args ) {
			$value = $this->get_value( $args );
			$multi = isset( $args['multi_select'] ) && $args['multi_select'] ? 'multiple' : '';
			$html  = sprintf( '<select %1$s class="wsd-select-box" name="%2$s%3$s" id="%2$s">', $multi, $args['id'], $multi ? '[]' : '' ); //todo add dynamic class
			foreach ( $args['options'] as $key => $label ) {
				if ( isset( $args['multi_select'] ) && $args['multi_select'] && is_array( $value ) ) {
					$html .= sprintf( '<option value="%s"%s>%s</option>', $key, in_array( $key, $value ) ? ' selected' : '', $label );
				} else {
					$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
				}
			}
			$html .= sprintf( '</select>' );
			$html .= $this->get_field_description( $args );

			return $html;
		}

		private function checkbox_input( $args ) {
			$value = $this->get_value( $args );
			$html  = sprintf( '<div class="wsd-select-box" id="%1$s">', $args['id'] ); //todo add dynamic class
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s"%3$s>%4$s <br>', $args['id'], $key, is_array( $value ) ? ( in_array( $key, $value ) ? ' checked'
					: '' ) : '',
					$label );
			}
			$html .= sprintf( '</div>' );
			$html .= $this->get_field_description( $args );

			return $html;
		}

		private function radio_input( $args ) {
			$value = $this->get_value( $args );
			$html  = '<fieldset>'; //todo add class and id
			foreach ( $args['options'] as $key => $label ) {
				$html .= sprintf( '<input type="radio" class="radio" name="%1$s" value="%2$s" %3$s/>', $args['id'], $key, checked(
					$value, $key, false ) );
				$html .= sprintf( '%1$s<br>', $label );
			}
			$html .= $this->get_field_description( $args );
			$html .= '</fieldset>';

			return $html;
		}

		private function create_form() {
			?>
			<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ) ?>"
			      method="POST"
			      id="<?php echo esc_attr( $this->form['id'] ) ?>"
			      class="<?php echo esc_attr( $this->form['class'] ) ?>">
				<?php //$this->submit_button(); ?>
				<table class="form-table">
					<?php
					foreach ( $this->inputs as $input_data ) {
						?>
						<tr class="wsd_text_<?php echo esc_attr( $input_data['class'] ) ?>"
						    id="wsd_text_<?php echo esc_attr( $input_data['id'] ) ?>">
							<?php
							if ( $input_data['type'] == 'html_separator' ) {
								//todo add class and id
								?>
								<td colspan="2">
									<br>
									<hr>
									<br>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'title' ) {
								//todo add class and id
								?>
								<td colspan="2">
									<h3><?php echo esc_html( $input_data['text'] ) ?></h3>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'text' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->text_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'upload' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->upload_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'email' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->email_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'color' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->color_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'url' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->url_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'textarea' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->textarea_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'number' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->number_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'password' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->password_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'select' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->select_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'checkbox' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->checkbox_input( $input_data ); ?>
								</td>
								<?php
							}
							if ( $input_data['type'] == 'radio' ) {
								?>
								<th class="titledesc">
									<label for="<?php echo esc_attr( $input_data['id'] ) ?>">
										<strong>
											<?php echo esc_html( $input_data['label'] ) ?>
										</strong>
									</label>
								</th>
								<td class="forminp forminp-text">
									<?php echo $this->radio_input( $input_data ); ?>
								</td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					if ( isset( $this->form['nonce_action'] ) && isset( $this->form['nonce_name'] ) ) {
						wp_nonce_field( $this->form['nonce_action'], $this->form['nonce_name'] );
					}
					?>
					<input type="hidden" name="action" value="<?php echo esc_attr( $this->action ); ?>">
				</table>
				<?php $this->submit_button(); ?>
			</form>
			<?php
		}

		private function submit_button() {
			?>
			<button type="submit" class="button-primary woocommerce-save-button">Save Changes</button>
			<?php
		}
	}
}