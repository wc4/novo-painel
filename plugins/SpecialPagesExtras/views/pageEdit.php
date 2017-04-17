<?php
// echo 'krowa';
// die();
  global $SITEURL,$TEMPLATE;
  global $data_edit; // SimpleXML to read from

  $id = @$_GET['id'];
  $isI18N = function_exists('return_i18n_page_structure');  //ZMIANA
  // determine special page type
  $spname = null;
  if (isset($_GET['special'])) {
    // create a special page or change the type of page
    $spname = $_GET['special'];
  } else if (isset($data_edit) && isset($data_edit->special) && (string) $data_edit->special) {
    // edit a special page
    $spname = (string) $data_edit->special;
  } else if ($isI18N && isset($_GET['newid']) && strpos($_GET['newid'],'_') > 0) {
    // this language page should be the same as the default language page
    $id_base = substr($_GET['newid'], 0, strrpos($_GET['newid'],'_'));
    $data_base = getXML(GSDATAPAGESPATH . $id_base . '.xml');
    if (isset($data_base) && isset($data_base->special) && (string) $data_base->special) {
      $spname = (string) $data_base->special;
    }
  }
  if ($spname) {
    $spdef = I18nSpecialPages::getSettings($spname);
	$speSettings = SPESettings::load($spname);
  }

  $creDate = @$data_edit->creDate ? (string) $data_edit->creDate : (string) @$data_edit->pubDate;
  $defs = null;
  if (@$spdef) {
    $defs = @$spdef['fields'];
    if (!$id && @$spdef['defaultcontent']) {
      global $content;
      $content = $spdef['defaultcontent'];
    }
  }
  echo '<input type="hidden" name="special-creDate" value="'.htmlspecialchars($creDate).'"/>';
  echo '<input type="hidden" name="post-special" value="'.htmlspecialchars($spname).'"/>';
  if (@$defs && count($defs) > 0) {
  	echo '<h2 class="text-center">Ficha de cadastro do imóvel</h2>';
    echo '<div class="spe_special-container col-md-12" style="clear:both">';
	$i = 0;
    foreach ($defs as $def) {
		$i++;
		$key = strtolower($def['name']);
		$label = $def['label'];
		$type = $def['type'];
		$value = htmlspecialchars($id ? (isset($data_edit->$key) ? $data_edit->$key : '') : (isset($def['value']) ? $def['value'] : ''), ENT_QUOTES);

		$hasWidth = @$speSettings['fieldsData'][$key]['cell-width'] != '';
		$w = @$speSettings['fieldsData'][$key]['cell-width'] . (@$speSettings['fieldsData'][$key]['cell-width-unit'] == 'percent' ? '' : 'px');
		if($key == 'codigo'){					
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-map-o" aria-hidden="true"></i>Informações básicas</h3>';
		}
		if($key == 'cep'){					
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-map-o" aria-hidden="true"></i>
 Localização do imóvel</h3>';
		}
		if($key == 'precocondominio'){
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-plus" aria-hidden="true"></i> Dados complementares</h3>';
		}
		if($key == 'areatotal'){
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-arrows-alt" aria-hidden="true"></i> Dimensões</h3>';
		}
		if($key == 'salasinfra'){
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-tag" aria-hidden="true"></i> Detalhes do imóvel</h3>';
		}
		if($key == 'foto'){
			echo '<h3 class="title col-md-12" style="box-shadow: inset 0 -2px 0 #c6c6c6;"><i class="fa fa-picture-o" aria-hidden="true"></i> Fotos do imóvel</h3>';
		}
		echo '<div class="spe_cell col-md-'.$w.'">';
		switch ($type){
			case 'text':
			case 'textfull':{
				if($key == 'codigo' && $value == ''){
					date_default_timezone_set('America/Sao_Paulo');
					$codigo = date("YmdHis");  
					echo '<b>'.$label.'</b><br />';
					echo '<input class="form-control input-sm" type="text" id="post-sp-'.$key.'" name="post-sp-'.$key.'" value="'.$codigo.'" />';
				}else{					
					echo '<b>'.$label.'</b><br />';
					echo '<input class="form-control input-sm" type="text" id="post-sp-'.$key.'" name="post-sp-'.$key.'" value="'.$value.'" />';
				}
				break; 
			}
  			case 'dropdown':{
				echo '<b>'.$label.'</b><br />';
				echo '<select id="post-sp-'.$key.'" name="post-sp-'.$key.'" class="form-control input-sm" >';
				foreach ($def['options'] as $option) {
					$attrs = $value == $option ? ' selected="selected"' : '';
					echo '<option'.$attrs.'>'.$option.'</option>';
				}
				echo '</select>';
				break;
			}
			case 'checkbox':{
				echo '<b>'.$label.'</b><br />';
				echo '<input type="checkbox" id="post-sp-'.$key.'" name="post-sp-'.$key.'" value="on" '.($value ? 'checked="checked"' : '').'/>'; 
				break; 
			}
			case "textarea":{
				echo '<b>'.$label.'</b><br />';
				echo '<textarea id="post-sp-'.$key.'" name="post-sp-'.$key.'" style="border: 1px solid #AAAAAA;">'.$value.'</textarea>'; 
				break;
			}
			case "wysiwyg":{
				echo '<b>'.$label.'</b><br />';
				echo '<textarea id="post-sp-'.$key.'" name="post-sp-'.$key.'" style="border: 1px solid #AAAAAA;">'.$value.'</textarea>'; 
				?>
					<script type="text/javascript">
					$(function() {
						<?php I18nSpecialPagesBackend::outputCKEditorJS('post-sp-'.$key, 'editor_'.$i); ?>
					});
					</script>
				<?php
				break;
			}
			case 'link':{
				echo '<b>'.$label.'</b><br />';
				echo '<input class="form-control input-sm" type="text" id="post-sp-'.$key.'" name="post-sp-'.$key.'" value="'.$value.'" />'; 
				echo '<span class="edit-nav"><a id="browse-'.$key.'" href="#">'.i18n_r('i18n_specialpages/BROWSE_PAGES').'</a></span>';
				?>
					<script type="text/javascript">
					function fill_sp_<?php echo $i; ?>(url) {
						$('#post-sp-<?php echo $key; ?>').val(url);
					}
					$(function() { 
						$('#browse-<?php echo $key; ?>').click(function(e) {
							e.preventDefault();
							window.open('<?php echo $SITEURL; ?>plugins/i18n_specialpages/browser/pagebrowser.php?func=fill_sp_<?php echo $i; ?>&i18n=<?php echo $isI18N; ?>', 'browser', 'width=800,height=500,left=100,top=100,scrollbars=yes');
						});
					});
					</script>
				<?php
				break; 
			}
			case 'image':
			case 'file':{
				echo '<b>'.$label.'</b><br />';
				echo '<input class="form-control input-sm" type="text" id="post-sp-'.$key.'" name="post-sp-'.$key.'" value="'.$value.'" />';
				echo ' <span class="edit-nav"><a id="browse-'.$key.'" href="#">'.($type=='image' ? i18n_r('i18n_specialpages/BROWSE_IMAGES') : i18n_r('i18n_specialpages/BROWSE_FILES')).'</a></span>';
				?>
					<script type="text/javascript">
						function fill_sp_<?php echo $i; ?>(url) {
						$('#post-sp-<?php echo $key; ?>').val(url);
						}
						$(function() { 
						$('#browse-<?php echo $key; ?>').click(function(e) {
						e.preventDefault();
						window.open('<?php echo $SITEURL; ?>plugins/i18n_specialpages/browser/filebrowser.php?func=fill_sp_<?php echo $i; ?>&type=<?php echo $type=='image' ?'images' : ''; ?>', 'browser', 'width=800,height=500,left=100,top=100,scrollbars=yes');
						});
						});
					</script>
				<?php
				break; 
			}
      }
	  
	  echo '</div>';
  	}		
    echo "</div>\r\n";
  }
  echo '<script type="text/javascript">';
  echo "$(function() {\r\n";
  if (@$spdef['slug']) {
    if (!$id && !@$_GET['newid']) {
      $slug = strftime($spdef['slug']);
      echo "$('#post-id').val(".json_encode($slug).").closest('p').hide();\r\n";
    } else {
      echo "$('#post-id').closest('p').hide();\r\n";
    }
  }
  if (@$spdef['parent']) {
    $parent = $spdef['parent'];
    echo "if ($('#post-parent').val(".json_encode($parent).").val() == ".json_encode($parent).") $('#post-parent').closest('p').hide();\r\n"; 
    echo "if (window['changeParent']) changeParent();\r\n"; //ZMIANA
  }
  if (@$spdef['tags'] && !$id) {
    $tags = $spdef['tags'];
    echo "$('#post-metak').val(".json_encode($tags).");\r\n";
  }
  if (@$spdef['template']) {
    $template = $spdef['template'];
    echo "if ($('#post-template').val(".json_encode($template).").val() == ".json_encode($template).") $('#post-template').closest('p').hide();\r\n"; 
  }
  $m = @$spdef['menu'];
  if (($m || $m == '0') && $isI18N) {
    if (!$id && $m == 'f') {
      echo "$('#post-menu-enable').attr('checked','checked');\r\n";
      echo "$('#post-menu-order option:first').attr('selected','selected');\r\n";
    } else if (!$id && $m == 'l') {
      echo "$('#post-menu-enable').attr('checked','checked');\r\n";
      echo "$('#post-menu-order option:last').attr('selected','selected');\r\n";
    }
    echo "$('#post-menu-enable').closest('p').hide();\r\n";
    if ($m == '0') {
      echo "$('#post-menu').closest('div').hide();\r\n";
    } else {
      echo "$('#menu-items').show();\r\n";
      echo "$('#post-menu-order').prev().add('#post-menu-order').hide();\r\n";
    }
  } else if ($m == '0') {
    echo "$('#post-menu-enable').closest('p').hide();\r\n";
    echo "$('#post-menu-order').closest('div').hide();\r\n";
  }
  echo "})\r\n";
  echo "</script>\r\n";
