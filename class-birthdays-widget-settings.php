<?php
	class Birthdays_Widget_Settings
	{
	    private $options;
	
	    public function __construct() {
	        add_action( 'admin_menu', array( &$this, 'add_plugin_page' ) );
	    }
	
	    public function add_plugin_page() {
	        add_menu_page( 'Birthdays Widget', 'Birthdays Widget', 'read', 'birthdays-widget', array( &$this, 'create_plugin_page' ) );
	        
	       // add_submenu_page( 'birthdays-widget', 'Overview', 'Overview', 'read', 'birthdays-widget-overview', array( &$this, 'create_submenu_page_overview' ) );
	        add_submenu_page( 'birthdays-widget', 'Import', 'Import', 'read', 'birthdays-widget-import', array( &$this, 'create_submenu_page_import' ) );
	        add_submenu_page( 'birthdays-widget', 'Export', 'Export', 'read', 'birthdays-widget-export', array( &$this, 'create_submenu_page_export' ) );
	    }
	
	    public function sanitize( $input ) {
	        $new_input = array( );
	        if( isset( $input['id_number'] ) )
	            $new_input['id_number'] = absint( $input['id_number'] );
	
	        if(isset($input['title']))
	            $new_input['title'] = sanitize_text_field( $input['title'] );
	
	        return $new_input;
	    }
	
	    public function print_section_info() {
	        print 'Enter your settings below:';
	    }
		
	    public function id_number_callback() {
	        printf(
	            '<input type="text" id="id_number" name="my_option_name[id_number]" value="%s" />',
	            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number'] ) : ''
	        );
	    }
	
	    public function title_callback() {
	        printf(
	            '<input type="text" id="title" name="my_option_name[title]" value="%s" />',
	            isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
	        );
	    }
	    
	    public function create_plugin_page() {
			global $wpdb;
			$setting_url = admin_url( 'admin.php' ) . '?page=birthdays-widget';
			
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
			
	    	if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'You do not have sufficient permissions to access this page.', 'birthdays-widget' ) );
			}
			
			echo '<div class="wrap">';
			echo 	'<h2><div id="icon-options-general" class="icon32"></div>'.__( 'Birthdays Widget Settings', 'birthdays-widget' ).
					'<a href="#birthday_name" class="add-new-h2">'. __( 'Add New', 'birthdays-widget' ) .'</a></h2>';
			
				$table_name = $wpdb->prefix . 'birthdays';
				
				if( isset( $_POST['birthdays_add_new'] ) ){
					if( !isset( $_POST['birthday_name'] ) || empty( $_POST['birthday_name'] ) || !isset( $_POST['birthday_date'] ) || empty( $_POST['birthday_date'] ))
						echo '<div id="message" class="error"><p>'. __( 'Please fill all the boxes!', 'birthdays-widget' ) .'</p></div>';
					else{
						//add the new entry
						$insert_query = "INSERT INTO $table_name (name, date) VALUES (%s, %s);";	
						if( $wpdb->query( $wpdb->prepare( $insert_query, $_POST['birthday_name'], date( 'Y-m-d' , strtotime($_POST['birthday_date'] ) ) ) ) == 1)
							echo '<div id="message" class="updated"><p>'. __( 'Your new record was added!', 'birthdays-widget' ) .'</p></div>';
						else 
							echo '<div id="message" class="error"><p>Query error</p></div>';
					}
				}
				
				if( isset( $_GET['action'] ) ){
					if( !isset( $_GET['id'] ) || empty( $_GET['id'] ) )
						echo '<div id="message" class="error"><p>'. __( 'There was an error!', 'birthdays-widget' ) .'</p></div>';
					
					elseif( $_GET['action'] == "delete" ){
						
						$delete_query = "DELETE FROM $table_name WHERE id = '%d' LIMIT 1;";
						
						if( $wpdb->query( $wpdb->prepare( $delete_query, $_GET['id'] ) ) == 1 )
							echo '<div id="message" class="updated"><p>'. __( 'The record was deleted!', 'birthdays-widget' ) .'</p></div>';
						else
							echo '<div id="message" class="error"><p>Query error</p></div>';
						
					}elseif( $_GET['action'] == "edit" ){
						
						if( isset( $_GET['do'] ) && $_GET['do'] == "save" && isset( $_POST['birthdays_edit'] ) ){
							//update the record
							if( !isset( $_POST['birthday_name'] ) || empty( $_POST['birthday_name'] ) || !isset( $_POST['birthday_date'] ) || empty( $_POST['birthday_date'] ) )
								echo '<div id="message" class="error"><p>'. __( 'Please fill all the boxes!', 'birthdays-widget' ) .'</p></div>';
							else {
								$update_query = "UPDATE $table_name SET name = '%s', date = '%s' WHERE id = '%d' LIMIT 1;";
							
								if( $wpdb->query( $wpdb->prepare( $update_query, $_POST['birthday_name'], date( 'Y-m-d' , strtotime( $_POST['birthday_date'] ) ), $_GET['id'] ) ) == 1)
									echo '<div id="message" class="updated"><p>'. __( 'The recoed was updated!', 'birthdays-widget' ) .'</p></div>';
							}
						}
						else{
							$select_query = "SELECT * FROM $table_name WHERE id = '%d' LIMIT 1;";
							
							$result = $wpdb->get_row( $wpdb->prepare( $select_query, $_GET['id'] ) );
							
							echo '<div id="edit">
									<form method="POST" action="'. $setting_url .'&action=edit&id='. $_GET['id'] .'&do=save">
										<label for="birthday_name">'. __( 'Name', 'birthdays-widget' ) .':</label><input type="text" maxlength="45" size="10" id="birthday_name" name="birthday_name" value="'. $result->name .'" />
										<label for="birthday_date">'. __( 'Date', 'birthdays-widget' ) .':</label><input type="text" size="10" id="birthday_date" name="birthday_date" value="'. date_i18n( 'd-m-Y', strtotime( $result->date ) ) .'" />
										<input name="save" type="submit" class="button-primary" value="'. __( 'Update', 'birthdays-widget' ) .'" />
										<input type="hidden" name="birthdays_edit" value="1" />
									</form>
								</div>';
						}
					}
				}
					
				echo '<div id="birthdays_list">'. __( 'All birthdays currenlty are', 'birthdays-widget' ) .': ';
				
				$query = "SELECT * FROM $table_name;";
				
				$results = $wpdb->get_results( $query );
				
				echo '<table class="widefat">
						<thead>
						    <tr>
						        <th>ID</th>
						        <th>'. __( 'Name', 'birthdays-widget' ).'</th>       
						        <th>'. __( 'Date', 'birthdays-widget' ).'</th>
						        <th>'. __( 'Action', 'birthdays-widget' ).'</th>
						    </tr>
						</thead>
						<tfoot>
						    <tr>
						 		<th>ID</th>
						        <th>'. __( 'Name', 'birthdays-widget' ).'</th>       
						        <th>'. __( 'Date', 'birthdays-widget' ).'</th>
						        <th>'. __( 'Action', 'birthdays-widget' ).'</th>
						    </tr>
						</tfoot>
						<tbody>';
				   
				
				foreach( $results as $row ){
					echo '<tr>
						     <td>'. $row->id .'</td>
						     <td>'. $row->name .'</td>
						     <td>'. date_i18n( get_option( 'date_format' ), strtotime( $row->date ) ) .'</td>
						     <td><a href="'. $setting_url .'&action=edit&id='. $row->id .'">'. __( 'Edit', 'birthdays-widget' ) .'</a> | <a class="delete_link" href="'. $setting_url .'&action=delete&id='. $row->id .'">'. __( 'Delete', 'birthdays-widget' ) .'</a></td>
				   
				   		</tr>';
				}
				
				echo '</tbody>
				</table>';
			
			echo '</div>';
			
			echo 	'<div id="add_new">
						<form method="POST" action="'. $setting_url .'">
							<label for="birthday_name">'. __( 'Name', 'birthdays-widget' ) .':</label><input type="text" maxlength="45" size="10" id="birthday_name" name="birthday_name" />
							<label for="birthday_date">'. __( 'Date', 'birthdays-widget' ) .':</label><input type="text" size="10" id="birthday_date" name="birthday_date" />
							<input name="save" type="submit" class="button-primary" value="'. __( 'Add', 'birthdays-widget' ) .'" />
							<input type="hidden" name="birthdays_add_new" value="1" />
						</form>
					</div>';
		
			echo '</div>';
			echo '	<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery("#birthday_date").datepicker({
							changeMonth: true,
      						changeYear: true,
	    					"dateFormat" : "dd-mm-yy"
	    				});
						jQuery(".delete_link").click(function(){
							return confirm("'. __( 'Are you sure you want to delete this record?', 'birthdays-widget' ) .'");
						});
					});
					</script>';
		}
		
		public function create_submenu_page_overview() {
			echo '	<div class="wrap">
						<textarea></textarea>
					
					</div>';
		}
		
		public function create_submenu_page_import() {
			if( isset( $_POST['birthdays_upload_file'] ) && $_POST['birthdays_upload_file'] == 1 ){
				if( !isset( $_FILES['uploadedfile'] ) ){
					echo '<div id="message" class="error"><p>'. __( 'Select a file first!', 'birthdays-widget' ) .'</p></div>';
				}else{
					
					$target_path = dirname( __FILE__ )."/uploads/";
					
					$target_path = $target_path . basename( $_FILES['uploadedfile']['name'] );
					
					if( move_uploaded_file( $_FILES['uploadedfile']['tmp_name'], $target_path ) ) {
						echo "The file ".  basename( $_FILES['uploadedfile']['name'] ) ." has been uploaded";
						
						$row = 0;
						if( FALSE !== ( $handle = fopen( $target_path, "r" ) ) ) {
							global $wpdb;
								
							$table_name = $wpdb->prefix . "birthdays";
							
							while( FALSE !== ( $data = fgetcsv( $handle, 1000, "," ) ) ) {
								if( 2 != count( $data ) ){
									echo 'Wrong csv format!<br/>';
									break;
								}
								$row++;
								
								$new_record['name'] = $data[0];
								$new_record['date'] = $data[1];
								
								//TODO meybe convert date format
								
								$wpdb->insert( 
											$table_name, 
											array( 
												'name' => $new_record['name'], 
												'date' => $new_record['date'] 
											), 
											array( 
												'%s', 
												'%s' 
											));
							}
							
							echo $row.' records inserted!<br/>';
							fclose($handle);
						}
						
						@unlink($target_path);
						
						echo '';
						
					} else{
						echo '<div id="message" class="error"><p>'. __( 'There was an error uploading the file, please try again!', 'birthdays-widget' ) .'</p></div>';
					}
				}
			}
			echo 	'<div class="wrap">
					<span>'. __( 'Here you can upload a CSV file with youw own data or from a plugin-export. (CSV must have format <name>,<date> as <name> an string and <date> as Y-m-D)', 'birthdays-widget' ) .'</span>
					<form action="" method="POST" enctype="multipart/form-data">
						<label for="uploadedfile">'. __( 'File', 'birthdays-widget' ) .'</label> <input type="file" name="uploadedfile" id="uploadedfile" accept=".csv" />
						<input name="upload" type="submit" class="button-primary" value="'. __( 'Upload', 'birthdays-widget' ) .'" />
						<input type="hidden" name="birthdays_upload_file" value="1" />
					</form>
					
					
					</div>';
			
		}
		
		public function create_submenu_page_export() {
			echo 	'<div class="wrap">
						<p>'. __( 'In order to download the export file press the button below', 'birthdays-widget' ) .'<br/>
							<a href="'. admin_url( 'admin-ajax.php' ) .'?action=get_birthdays_export_file" target="_blank" class="button-primary" id="birthdays-export-button">'. __( 'Download', 'birthdays-widget' ) .'</a>
						</p>
					</div>';
		}
		
	}
	