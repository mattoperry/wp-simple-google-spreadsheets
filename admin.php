<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Simple Google Spreadsheets</h2>

	<form method="post" action="options.php"> 
		<?php 
			settings_fields( 'simple-google-spreadsheets' );
			do_settings_fields( 'simple-google-spreadsheets' );

			$sheets = get_option('simple-google-spreadsheets_sheets');
		?>

		<table class="form-table">
			<thead>
				<th scope="col">Name</th>
				<th scope="col">Spreadsheet ID</th>
				<th scope="col">Worksheet ID</th>
				<th scope="col">Refresh (seconds)</th>
				<th scope="col"></th>
			</thead>
			<tbody>

				<?php 

					//are we starting off with no settings?
					$blank = ( empty( $sheets ) )  ? true : false;
					$num_sheets = ($blank) ? 0 : count( $sheets );

					for ( $i=0; $i<=$num_sheets; $i++ ) {

						$new = ( ( $i == $num_sheets ) || $blank );

						$name 		= ( !$new ) ? $sheets[$i]['name'] 		: '';
						$ID 		= ( !$new ) ? $sheets[$i]['ID']			: '';
						$worksheet 	= ( !$new ) ? $sheets[$i]['worksheet']	: '';
						$refresh 	= ( !$new ) ? $sheets[$i]['refresh']			: '300';

				?>

				<tr id="sheet_<?php echo $i; ?>" <?php if ($new) echo 'class="marked_for_deletion new_sheet hidden"';?> data="<?php echo $i; ?>">
					<td><input name="simple-google-spreadsheets_sheets[<?php echo $i; ?>][name]" type="text" value="<?php echo $name; ?>"></td>
					<td><input name="simple-google-spreadsheets_sheets[<?php echo $i; ?>][ID]" type="text" value="<?php echo $ID; ?>" class="regular-text"></td>
					<td><input name="simple-google-spreadsheets_sheets[<?php echo $i; ?>][worksheet]" type="text" value="<?php echo $worksheet; ?>" class="small-text"></td>
					<td><input name="simple-google-spreadsheets_sheets[<?php echo $i; ?>][refresh]" type="text" value="<?php echo $refresh; ?>" class="small-text"></td>
					<td>
						<input name="simple-google-spreadsheets_sheets[<?php echo $i; ?>][delete]" type="hidden" class="row_delete" value="<?php if ($new) echo '1'; else echo '0';?>">
						<button class="button-secondary <?php if ($new) echo 'cancel_add_sheet'; else echo 'delete_sheet';?>" data="<?php echo $i; ?>"><?php if ($new) echo "cancel"; else echo "delete";?></button>
						<button class="button-secondary <?php if (!$new) echo 'undo_delete_sheet';?>" data="<?php echo $i; ?>" style="display:none;">undo</button>
					</td>
				</tr>

					<?php 
						//if ( $blank ) break;
					} //end for ?> 
				<tr><td>
					<button id="add_sheet" class="add_sheet button-secondary">Add Sheet</button>
				</td></tr>
			</tbody>
		</table>

		<?php
		submit_button();
		?>
		
	</form>
</div>