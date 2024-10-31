<?php
$ntdb_CurrentVersion = get_option('ntdbCurrentVersion');
$ntdb_CurrentType = get_option('ntdbCurrentType');

$opt_TinyDB = get_option('optTinyDB');
$strIdBD = ntdb_GetSlug($opt_TinyDB);
$tmpPos = strpos($strIdBD,".csv"); if ($tmpPos) $strIdBD = substr($strIdBD,0,$tmpPos);
$tmpPos = strrpos($strIdBD,"/"); if ($tmpPos) $strIdBD = substr($strIdBD,$tmpPos+1);

if ($opt_TinyDB != "")
   { $tmpOpt = "optPathFileBD_" . $strIdBD;     $PathBD = get_option($tmpOpt);
     $tmpOpt = "optIsTitle_" . $strIdBD;        $opt_IsTitle = get_option($tmpOpt,1);
     $tmpOpt = "optFieldsSeparator_" . $strIdBD; $opt_FieldsSeparator = get_option($tmpOpt); if (!$opt_FieldsSeparator) $opt_FieldsSeparator = 1;
     $ntdb_FieldsChar = ($opt_FieldsSeparator==1?";":",");
     $tmpOpt = "optSearchField_" . $strIdBD;    $opt_SearchField = get_option($tmpOpt); if (!$opt_SearchField) $opt_SearchField = 2;
     $tmpOpt = "optPartSearch_" . $strIdBD;     $opt_PartSearch = get_option($tmpOpt);
     $tmpOpt = "optCaseSearch_" . $strIdBD;     $opt_CaseSearch = get_option($tmpOpt);
     $tmpOpt = "optShowHead_" . $strIdBD;       $opt_ShowHead = get_option($tmpOpt);
     $tmpOpt = "optLineSpace_" . $strIdBD;      $opt_LineSpace = get_option($tmpOpt);
     $tmpOpt = "optNewPage_" . $strIdBD;        $opt_NewPage = get_option($tmpOpt);
     $tmpOpt = "optImgPos_" . $strIdBD;         $opt_ImgPos = get_option($tmpOpt); if (!$opt_ImgPos) $opt_ImgPos = "left";
     $tmpOpt = "optImgWidth_" . $strIdBD;       $opt_ImgWidth = get_option($tmpOpt); if (!$opt_ImgWidth) $opt_ImgWidth = "35";
   }

echo '<div align="right">' . esc_attr($ntdb_CurrentType) . ' Version v.' . esc_attr($ntdb_CurrentVersion) . '</div>';

if ($opt_TinyDB != "")
   { $tmpTab = sanitize_text_field($_GET['tab']);
     $tab = (isset($tmpTab) and $tmpTab != "")?$tmpTab:'ntdb_settings';
   }
else
  $tab = 'ntdb_databases';

$upload_dir = wp_upload_dir(); 
$tmpPath = $upload_dir['path'];
$tmpPos = strpos($tmpPath,"/uploads");
$tmpCommonPath = substr($tmpPath,0,$tmpPos);   
 
if (file_exists($PathBD))
   { $fd = fopen($PathBD,"r");
     if (!feof($fd)) $tmp1stLine = fgets($fd);

     $list1stLine = explode($ntdb_FieldsChar,$tmp1stLine);
     $NbRows = count($list1stLine);

     if ($opt_IsTitle)
        { for($i=1;$i<=$NbRows;$i++)
             { $A_Head[$i] = esc_attr(trim(utf8_encode($list1stLine[$i-1])));
             }
        }
     else
        { for($i=1;$i<=$NbRows;$i++)
             $A_Head[$i] = "Col" . $i;
        }
   
     if (!is_dir($tmpCommonPath . "/NTDB")) mkdir($tmpCommonPath . "/NTDB",0755);
     $tmpPos = strrpos($opt_TinyDB,"/");
     $tmpDataFileName = substr($opt_TinyDB, $tmpPos+1,-4);
     $tmpDataFileName = strtolower($tmpDataFileName);
     $tmpDataFileName = str_replace(" ","-",$tmpDataFileName);
     if (!is_dir($tmpCommonPath . "/NTDB/" . $tmpDataFileName)) mkdir($tmpCommonPath . "/NTDB/" . $tmpDataFileName,0755);
         
     for($i=1;$i<=$NbRows;$i++)
        { $tmpDir = $A_Head[$i];
          $tmpDir = strtolower($tmpDir);
          $tmpDir = str_replace(" ","-",$tmpDir);
          if (!is_dir($tmpCommonPath . "/NTDB/" . $tmpDataFileName . "/" . $tmpDir)) mkdir($tmpCommonPath . "/NTDB/" . $tmpDataFileName . "/" . $tmpDir,0755);
        }

     fclose($fd);
   }
else
   { echo '<font color="#ff0000">';esc_html_e('No database found! Please, first select or upload a CSV data file...','next-tiny-db'); echo '</font><br>';
   }
   
for($i=1;$i<=$NbRows;$i++)
   { $tmpOpt = 'optRow_' . $strIdBD . "_" . $i;
     ${'opt_Row_'.$i} = get_option($tmpOpt); if (${'opt_Row_'.$i} == "") ${'opt_Row_'.$i} = "#000000";
     $tmpOpt = 'optRowLnk_' . $strIdBD . "_" . $i;
     ${'opt_RowLnk_'.$i} = get_option($tmpOpt);
     $tmpOpt = 'optRowBold_' . $strIdBD . "_" . $i;
     ${'opt_RowBold_'.$i} = get_option($tmpOpt);
     $tmpOpt = 'optRowEm_' . $strIdBD . "_" . $i;
     ${'opt_RowEm_'.$i} = get_option($tmpOpt);
     $tmpOpt = 'optRowHidden_' . $strIdBD . "_" . $i;
     ${'opt_RowHidden_'.$i} = get_option($tmpOpt);
   }
?>

<div class="wrap">
<nav class="nav-tab-wrapper">
     <a href="?page=ntdb-acp&tab=ntdb_databases" class="nav-tab <?php if($tab==='ntdb_databases'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Databases','next-tiny-db'); ?></a>
     <a href="?page=ntdb-acp&tab=ntdb_settings" class="nav-tab <?php if($tab==='ntdb_settings'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Settings','next-tiny-db'); ?></a>
     <a href="?page=ntdb-acp&tab=ntdb_help" class="nav-tab <?php if($tab==='ntdb_help'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Help','next-tiny-db'); ?></a>
</nav>

    <div class="tab-content">
    <?php switch($tab)
          { case 'ntdb_settings': ?> 
    <form method="post" action="options.php">
    <?php settings_fields('ntdb-settings-group'); ?>
    <?php do_settings_sections('ntdb-settings-group'); ?>

    <h2 class="title"><?php esc_html_e('Data file','next-tiny-db'); echo " [" . esc_attr($strIdBD) . "]"; ?></h2>
     
    <table class="form-table">
        <?php $tmpOpt = "optIsTitle_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('My data have headers','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_IsTitle==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Check if first line of your data file is done from the header titles of your columns','next-tiny-db'); ?>
        </td></tr>
        
        <?php $tmpOpt = "optFieldsSeparator_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Fields separator','next-tiny-db'); ?></th>
        <td><input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_FieldsSeparator==1?"checked ":"");?> /> <?php esc_html_e('Semicolon','next-tiny-db'); ?><br>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value=2 <?php echo($opt_FieldsSeparator==2?"checked ":"");?> /> <?php esc_html_e('Comma','next-tiny-db'); ?><br>
            <?php esc_html_e('Select what is the fields separator used in your data file','next-tiny-db'); ?>
        </td></tr>
    </table>

    <h2 class="title"><?php esc_html_e('Search','next-tiny-db'); ?></h2>
 
    <table class="form-table">   
        <?php $tmpOpt = "optSearchField_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Search field','next-tiny-db'); ?></th>
        <td><input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_SearchField==1?"checked ":"");?> /> <?php esc_html_e('Text box','next-tiny-db'); ?><br>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value=2 <?php echo($opt_SearchField==2?"checked ":"");?> /> <?php esc_html_e('List box','next-tiny-db'); ?><br>
            <?php esc_html_e('Select how the search value is entered','next-tiny-db'); ?>
        </td></tr>
        
        <?php $tmpOpt = "optPartSearch_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Search condition','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_PartSearch==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Check if search value could be part of the key, and not only the exact key','next-tiny-db'); ?>
            <br><em><font color="#808080"><?php esc_html_e('First record found will be displayed.','next-tiny-db'); ?></font></em>
        </td></tr>

        <?php $tmpOpt = "optCaseSearch_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Match case','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_CaseSearch==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Check if search is case sensitive','next-tiny-db'); ?>
        </td></tr>
    </table>    
    
    <h2 class="title"><?php esc_html_e('Fields','next-tiny-db'); ?></h2>    
    
    <table class="form-table">
        <?php $tmpOpt = "optShowHead_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Display headers','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_ShowHead==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Check to show titles of the columns','next-tiny-db'); ?>
        </td></tr>    

        <?php $tmpOpt = "optLineSpace_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Line space','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_LineSpace==1?"checked ":"");?>class="wppd-ui-toggle" /> <?php esc_html_e('Check to leave a line space between parts','next-tiny-db'); ?>
        </td></tr>  
          
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Style','next-tiny-db'); ?></th>
        <td>
        <?php
        echo '<table>';
        for($i=1;$i<=$NbRows;$i++)
           { echo '<tr><td><b>' . esc_attr(ntdb_tolower(utf8_decode($A_Head[$i]))) . '</b></td><td>';
             $tmpOptRow = 'optRow_' . $strIdBD . '_' . $i;
             echo '<input type="color" name="' . esc_attr($tmpOptRow) . '" value="' . esc_attr(${'opt_Row_'.$i}) . '" class="xxx" /> ';
             $tmpOptRow = 'optRowBold_' . $strIdBD . '_' . $i;
             $strCheck = (${'opt_RowBold_' . $i}==1?" checked":"");
             echo '<input type="checkbox" name="' . esc_attr($tmpOptRow) . '" value="1"' . esc_attr($strCheck) . ' class="wppd-ui-toggle" />'; esc_html_e('Bold','next-tiny-db');
             $tmpOptRow = 'optRowEm_' . $strIdBD . '_' . $i;
             $strCheck = (${'opt_RowEm_' . $i}==1?" checked":"");
             echo '<input type="checkbox" name="' . esc_attr($tmpOptRow) . '" value="1"' . esc_attr($strCheck) . ' class="wppd-ui-toggle" />'; esc_html_e('Italic','next-tiny-db');
             $tmpOptRow = 'optRowHidden_' . $strIdBD . '_' . $i;
             $strCheck = (${'opt_RowHidden_' . $i}==1?" checked":"");
             echo '<input type="checkbox" name="' . esc_attr($tmpOptRow) . '" value="1"' . esc_attr($strCheck) . ' class="wppd-ui-toggle" /> '; esc_html_e('Hidden','next-tiny-db');
             echo '</td></tr>';
           }
        echo '</table>';
        ?>
        </td></tr>
    </table>

    <h2 class="title"><?php esc_html_e('Image','next-tiny-db'); ?></h2>

    <table class="form-table">
        <?php $tmpOpt = "optImgPos_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Image position','next-tiny-db'); ?></th>
        <td><em><font color="#808080"><?php esc_html_e('Better for vertical images','next-tiny-db'); ?>:</font></em><br>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value="left" <?php echo($opt_ImgPos=="left"?"checked ":"");?> /> <?php esc_html_e('Left','next-tiny-db'); ?>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value="right" <?php echo($opt_ImgPos=="right"?"checked ":"");?> /> <?php esc_html_e('Right','next-tiny-db'); ?>
            <br><em><font color="#808080"><?php esc_html_e('Better for horizontal images','next-tiny-db'); ?>:</font></em><br>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value="top" <?php echo($opt_ImgPos=="top"?"checked ":"");?> /> <?php esc_html_e('Top','next-tiny-db'); ?>
            <input type="radio" name="<?php echo esc_attr($tmpOpt); ?>" value="bottom" <?php echo($opt_ImgPos=="bottom"?"checked ":"");?> /> <?php esc_html_e('Bottom','next-tiny-db'); ?>
            
        </td></tr>
       
        <?php $tmpOpt = "optImgWidth_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Image width','next-tiny-db'); ?></th>
        <td><input type="number" size="2" min="25" max="75" name="<?php echo esc_attr($tmpOpt); ?>" value="<?php echo esc_attr($opt_ImgWidth);?>" />%
            <br><em><font color="#808080"><?php esc_html_e('Select the width of the image beetween 25% to 75% of the page','next-tiny-db'); ?></font></em>
        </td></tr>
    </table>
             
    <h2 class="title"><?php esc_html_e('SEO','next-tiny-db'); ?></h2>

    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Internal links','next-tiny-db'); ?></th>
        <td>
        <?php
        echo '<table>';
        for($i=1;$i<=$NbRows;$i++)
           { echo '<tr><td><b>' . esc_attr(ntdb_tolower(utf8_decode($A_Head[$i]))) . '</b></td><td>';
             $tmpOptRow = 'optRowLnk_' . $strIdBD . '_' . $i;
             esc_html_e('linked to page slug','next-tiny-db');
             echo ' <input type="text" name="' . esc_attr($tmpOptRow) . '" value="' . esc_attr(${'opt_RowLnk_' . $i}) . '" />';
             echo '</td></tr>';
           }
        echo '</table>';
        echo '<br><em><font color="#808080">' . esc_html__('Left empty if not used','next-tiny-db') . '</font></em>';
        ?>
        </td></tr>
        
        <?php $tmpOpt = "optNewPage_" . $strIdBD; ?>
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Open in a new page','next-tiny-db'); ?></th> 
        <td><input type="checkbox" name="<?php echo esc_attr($tmpOpt); ?>" value=1 <?php echo($opt_NewPage==1?"checked ":"");?>class="wppd-ui-toggle" />
        </td></tr>
    </table>
       
    <?php submit_button(esc_html__('Save','next-tiny-db')); ?>
</form>
        <?php break;   
        
    case 'ntdb_databases': ?> 
    <form method="post" action="options.php">
    <?php settings_fields('ntdb-databases-group'); ?>
    <?php do_settings_sections('ntdb-databases-group'); ?>

    <h2 class="title"><?php esc_html_e('Database','next-tiny-db'); if ($strIdBD != "") echo ' [' . esc_attr($strIdBD) . ']'?></h2>

    <?php
    if ($opt_TinyDB != "")
       { $pos = strrpos($opt_TinyDB,"/");
         $DataFile = substr($opt_TinyDB,$pos+1);
             
         $pos = strpos($opt_TinyDB,"uploads");
         $pos = strpos($opt_TinyDB,"/",$pos+1);
         $tmpDataFilePath = substr($opt_TinyDB,$pos+1);
             
         $upload_path = wp_upload_dir(); $path = $upload_path['basedir'];
         $FullPathBD = $path . '/' . $tmpDataFilePath;

         if (!file_exists($FullPathBD))
            { $tmpFilePathFound = false;
              echo '<font color="#ff0000">';
              echo esc_attr($FullPathBD) . ": <b>"; esc_html_e('CSV data file not found!','next-tiny-db'); echo "</b><br>";
              echo '</font>';
            }
         else
            { $tmpFilePathFound = true;
              $tmpOptFileNameBD = "optPathFileBD_" . $strIdBD;
              register_setting('ntdb-databases-group',$tmpOptFileNameBD);
              update_option($tmpOptFileNameBD,$FullPathBD); 
            }
         ?>
         
         <table class="form-table">
         <?php
         if ($tmpFilePathFound)
            { ?>
            <tr valign="top">
            <th scope="row"><?php esc_html_e('Data file URL','next-tiny-db'); ?></th>
            <td valign=center>
            <?php echo esc_attr($opt_TinyDB); ?>
            </td></tr>
         
            <tr valign="top">
            <th scope="row"><?php esc_html_e('Data file path','next-tiny-db'); ?></th>
            <td valign=center>
            <?php echo esc_attr($FullPathBD); ?>
            </td></tr><?php
            }
       }
    else
       { echo '<table class="form-table">';
       }?>

    <tr valign="top">
    <th scope="row"><?php esc_html_e('New tiny DB','next-tiny-db'); ?></th>
    <td valign=center><div id="idsession">
    <?php
    if ($tmpFilePathFound)
       { echo "<div id=\"divTinyDB\" name=\"divTinyDB\">" . esc_attr($opt_TinyDB) . "</div><br>";
         echo "<input name=\"optTinyDB\" id=\"optTinyDB\" xtype=\"text\" type=\"hidden\" value=\"" . esc_attr($opt_TinyDB) . "\">";
         echo "<input name=\"ntdb-upload-button\" id=\"ntdb-upload-button\" type=\"button\" class=\"button\" value=\"" . esc_html__('Change CSV data file','next-tiny-db') . "\"><br><em><font color=\"#808080\">" . esc_html__('Press Save button to save changes.','next-tiny-db') . "</font></em>";
       }
    else         
       { echo "Upload CSV data file to use as tiny DB<br>";
         echo "<div id=\"divTinyDB\" name=\"divTinyDB\">" . esc_attr($opt_TinyDB) . "</div><br>";
         echo "<input name=\"optTinyDB\" id=\"optTinyDB\" xtype=\"text\" type=\"hidden\">";
         echo "<input name=\"ntdb-upload-button\" id=\"ntdb-upload-button\" type=\"button\" class=\"button\" value=\"" . esc_html__('Upload CSV data file','next-tiny-db') . "\"><br><em><font color=\"#808080\">" . esc_html__('Press Save button to save changes.','next-tiny-db') . "</font></em>";
       }
    ?>
    </div>
    </td></tr>
    </table> 
<?php
submit_button(esc_html__('Save','next-tiny-db'));
?> 
</form>
    <?php break;
    
    case 'ntdb_help': ?> 
         
    <h2 class="title"><?php esc_html_e('Shortcode','next-tiny-db'); ?></h2>
    
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Example','next-tiny-db'); ?></th> 
        <td>[next_tiny_db db_name="Cars" title="My cars" key="Color" form_show="No" table_results="Yes" max_results="10"]
            <br><em><font color="#808080"><?php esc_html_e('Insert the shortcode into a widget or a page to display the search form then the results','next-tiny-db'); ?></font></em><br>
        </td></tr>
        
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Parameters','next-tiny-db'); ?></th>
        <td>
        <b>db_name</b> : <?php esc_html_e('CSV filename (with or without the .csv extension)','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> db_name="cars" for cars.csv data file</font></em><br>
        <p> 
        <b>title</b> : <?php esc_html_e('Title of database to be displayed on top of the search field on the web page','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> title="My cars"</font></em><br>

        <p> 
        <b>key</b> : <?php esc_html_e('Header or Number (starting to 1) of the column in which the key will be searched','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> key="Color" <?php esc_html_e('to propose a list of colors as the search field','next-tiny-db'); ?></font></em><br>

        <p> 
        <b>form_show</b> : <?php esc_html_e('Set to "yes" or "no", "true" or "false", "1" or "0" to display (defult value) or hide the search form on top of results','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> form_show="No"</font></em><br>

        <p> 
        <b>table_results</b> : <?php esc_html_e('Set to "yes" or "no", "true" or "false", "1" or "0" to display the results in a table or a vertical view','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> table_results="Yes"</font></em><br>

        <p> 
        <b>max_results</b> : <?php esc_html_e('Max number of results displayed on the page (default is 30)','next-tiny-db'); ?><br>
        <em><font color="#808080"><?php esc_html_e('For example: ','next-tiny-db'); ?> max_results="10"</font></em><br>
        </td></tr>
                
        <tr valign="top">
        <th scope="row"><?php esc_html_e('Usage','next-tiny-db'); ?></th>
        <td>
        <?php esc_html_e('You can have several pages with different shortcodes using the same database for different results according to the searched column.','next-tiny-db'); ?><br>
        <?php esc_html_e('For example you may have choose through the plugin to display single result with a primary key (unique identification number of a car) in a view on a page A.','next-tiny-db'); ?><br>
        <?php esc_html_e('Then on page B you can display several results searching with a secondary key (color of the cars) in a table with the table_results parameter set to "Yes" in the shortcode.','next-tiny-db'); ?><br>
        <?php esc_html_e('You can also hide the search form with the form_show parameter set to "No" in the shortcode, to display your own pre_search links.','next-tiny-db'); ?><br>
        </td></tr>
    </table>
         
         <?php
        
    break;

    default:
    break;
        } ?>
  </div>
</div>