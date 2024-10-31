<?php
if (!defined('NTDB_KEY_DONATE'))
   { define('NTDB_KEY_DONATE','5YV9EPVGVPJHQ');
   }
if (!defined('NTDB_PLUGIN_NAME'))
   { define('NTDB_PLUGIN_NAME','Next Tiny DB');
   }
if (!defined('NTDB_PLUGIN_SLUG'))
   { define('NTDB_PLUGIN_SLUG','next-tiny-db');
   }
if (!defined('NTDB_VERSION'))
   { define('NTDB_VERSION', '2.2.1');
   }
if (!defined('NTDB_TYPE'))
   { define('NTDB_TYPE', 'Free');
   }
if (!defined('NTDB_PLUGIN_PAGE'))
   { define('NTDB_PLUGIN_PAGE','ntdb-acp');
   }

$upload_dir = wp_upload_dir();  
$tmpPath = $upload_dir['path'];
$tmpPos = strpos($tmpPath,"/uploads");
$gCommonPath = substr($tmpPath,0,$tmpPos);

function ntdb_GetFilePath($parTinyDB)
{ $upload_dir = wp_upload_dir();   
  $UploadPathWP = $upload_dir['basedir'];
  $tmpPos = strpos($UploadPathWP,"/uploads");
  $tmp1 = substr($UploadPathWP,0,$tmpPos);
  $tmpPos = strpos($parTinyDB,"/uploads");
  $tmp2 = substr($parTinyDB,$tmpPos);
  return $tmp1.$tmp2;
}

function ntdb_tolower($parStr)
{ $A_Accent = array('Ž','Š','ß','Ç','Æ','Œ','Ñ','Â','Ê','Î','Ô','Û','À','È','Ì','Ò','Ù','Á','É','Í','Ó','Ú','Ä','Ë','Ï','Ö','Ü');
  $A_Lower = array('ž','š','ss','ç','æ','œ','ñ','â','ê','î','ô','û','à','è','ì','ò','ù','á','é','í','ó','ú','ä','ë','ï','ö','ü');
  $tmptr = str_replace($A_Accent, $A_Lower, $parStr);
  return ucfirst(strtolower(utf8_encode($tmptr)));
}

$NumLines = 0;
$NbRows = 0;
$DataFileName = "";
$lineHeaders = "";
$opt_TinyDB = get_option('optTinyDB'); 
$tmpFilePath = ntdb_GetFilePath($opt_TinyDB);
$strIdBD = ntdb_GetSlug($opt_TinyDB);
$tmpPos = strpos($strIdBD,".csv"); if ($tmpPos) $strIdBD = substr($strIdBD,0,$tmpPos);
$tmpPos = strrpos($strIdBD,"/"); if ($tmpPos) $strIdBD = substr($strIdBD,$tmpPos+1);

$opt_IsTitle = get_option('optIsTitle_'.$strIdBD);
$opt_FieldsSeparator = get_option("optFieldsSeparator_" . $strIdBD); if (!$opt_FieldsSeparator) $opt_FieldsSeparator = 1;
$ntdb_FieldsChar = ($opt_FieldsSeparator==1?";":",");
     
if (file_exists($tmpFilePath))
   { $tmpPos = strrpos($opt_TinyDB,"/");
     $DataFileName = substr($opt_TinyDB, $tmpPos+1,-4);
     $DataFileName = strtolower($DataFileName);
     $DataFileName = str_replace(" ","-",$DataFileName);
     
     $fd = fopen($tmpFilePath,"r");
     while(!feof($fd))
          { $line = fgets($fd);
            if (trim($line) != "")
               { $NumLines++;
                 $listLine = explode($ntdb_FieldsChar,$line);
          
                 if ($NumLines == 1)
                    { $NbRows = count($listLine);
                      if ($opt_IsTitle)
                         { $tmpStartLine = 0;
                           for ($i=1;$i<=$NbRows;$i++)
                               { $A_TinyHeader[$i] = esc_attr(trim(utf8_encode($listLine[$i-1])));
                                 $A_TinyHeader_slug[$i] = strtolower($A_TinyHeader[$i]);
                                 $A_TinyHeader_slug[$i] = str_replace(" ","-",$A_TinyHeader_slug[$i]);
                               }
                         }
                      else
                         { $tmpStartLine = 1;
                           for ($i=1;$i<=$NbRows;$i++)
                               { $A_TinyHeader[$i] = "Col " . $i;
                                 $A_TinyHeader_slug[$i] = "col-" . $i;
                                 $A_TinyDB[$tmpStartLine][$i] = esc_attr(trim($listLine[$i-1])); 
                               } 
                         }
                    }
                 else   
                    { $tmpStartLine++;
                      for ($i=1;$i<=$NbRows;$i++)
                          { $A_TinyDB[$tmpStartLine][$i] = esc_attr(trim(utf8_encode($listLine[$i-1])));
                          }
                    }
               }
          }
     if ($opt_IsTitle) $NumLines--;
     fclose($fd);
   }

$tmpPath = $upload_dir['path'];
$tmpPos = strpos($tmpPath,"/uploads");
$gCommonPath = substr($tmpPath,0,$tmpPos);   

add_action('admin_enqueue_scripts', 'ntdb_Styles');
function ntdb_Styles()
{ $tmpStr = plugins_url('/',__FILE__);
  if (substr($tmpStr,-1) == "/")
     $tmpPos = strrpos($tmpStr,'/',-2);
  else   
     $tmpPos = strrpos($tmpStr,'/',-1);
  $tmpStr = substr($tmpStr,0,$tmpPos);
  $tmpPathCSS = $tmpStr . '/css/style.css';

  wp_enqueue_style('ntdb_style_css', $tmpPathCSS);
}

add_action('plugins_loaded', 'ntdb_checkVersion');
function ntdb_CheckVersion()
{ $tmpCurVersion = get_option('ntdbCurrentVersion');
  $tmpCurType = get_option('ntdbCurrentType');
  if((version_compare($tmpCurVersion, NTDB_VERSION, '<')) or (NTDB_TYPE !== $tmpCurType))
    { ntdb_PluginActivation();
    }
}

function ntdb_PluginActivation()
{ update_option('ntdbCurrentVersion', NTDB_VERSION);
  update_option('ntdbCurrentType', NTDB_TYPE);
  
  return NTDB_VERSION;
}
register_activation_hook(__FILE__, 'ntdb_PluginActivation');

add_action( 'admin_menu','ntdb_Add_Menu');
function ntdb_Add_Menu()
{ add_menu_page(
      'Next Tiny DB',
      NTDB_PLUGIN_NAME,
      'manage_options',
      'ntdb-acp',
      'ntdb_acp_callback',
      'dashicons-database-view'
    );
  
  add_submenu_page('ntdb-acp', __('Databases','next-tiny-db'), __('Databases','next-tiny-db'), 'manage_options', 'ntdb-acp&tab=ntdb_databases', 'render_generic_settings_page');
  add_submenu_page('ntdb-acp', __('Settings','next-tiny-db'), __('Settings','next-tiny-db'), 'manage_options', 'ntdb-acp&tab=ntdb_settings', 'render_generic_settings_page');
  add_submenu_page('ntdb-acp', __('Help','next-tiny-db'), __('Help','next-tiny-db'), 'manage_options', 'ntdb-acp&tab=ntdb_help', 'render_generic_settings_page');

	add_action('admin_init','register_ntdb_settings');  
}

add_action('init','ntdb_load_textdomain');
function ntdb_load_textdomain()
{ load_plugin_textdomain('next-tiny-db',false,NTDB_PLUGIN_SLUG . '/languages/'); 
}

function register_ntdb_settings()
{ global $NbRows;
  global $strIdBD;
  
  register_setting('ntdb-settings-group','ntdbCurrentVersion');
  register_setting('ntdb-settings-group','ntdbCurrentType');
  
  register_setting('ntdb-settings-group','optIsTitle_'.$strIdBD);
  register_setting('ntdb-settings-group','optFieldsSeparator_'.$strIdBD);;
  register_setting('ntdb-settings-group','optSearchField_'.$strIdBD);
  register_setting('ntdb-settings-group','optPartSearch_'.$strIdBD);
  register_setting('ntdb-settings-group','optCaseSearch_'.$strIdBD);

  register_setting('ntdb-settings-group','optShowHead_'.$strIdBD);
  register_setting('ntdb-settings-group','optLineSpace_'.$strIdBD);
  register_setting('ntdb-settings-group','optNewPage_'.$strIdBD);
    
  for($i=1;$i<=$NbRows;$i++)
     { $tmpOptRow = 'optRow_' . $strIdBD . "_" . $i;
       register_setting('ntdb-settings-group',$tmpOptRow);
       $tmpOptRow = 'optRowLnk_' . $strIdBD . "_" . $i;
       register_setting('ntdb-settings-group',$tmpOptRow);
       $tmpOptRow = 'optRowBold_' . $strIdBD . "_" . $i;
       register_setting('ntdb-settings-group',$tmpOptRow);
       $tmpOptRow = 'optRowEm_' . $strIdBD . "_" . $i;
       register_setting('ntdb-settings-group',$tmpOptRow);
       $tmpOptRow = 'optRowHidden_' . $strIdBD . "_" . $i;
       register_setting('ntdb-settings-group',$tmpOptRow);
     }
  register_setting('ntdb-settings-group','optImgPos_'.$strIdBD);
  register_setting('ntdb-settings-group','optImgWidth_'.$strIdBD);
  
  register_setting('ntdb-databases-group','optTinyDB');
}

function ntdb_acp_callback()
{ global $title;

  if (!current_user_can('administrator'))
     { wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	   }
	
  print '<div class="wrap">';
  print "<h1 class=\"stabilo\">$title</h1><hr>";

  $file = plugin_dir_path( __FILE__ ) . "ntdb-acp-page.php";
  if (file_exists($file))
      require $file;

  echo "<p><em><b>" . esc_html__('Add for free nice other','next-tiny-date') . " <a target=\"_blank\" href=\"https://nxt-web.com/wordpress-plugins/\" style=\"color:#FE5500;font-weight:bold;font-size:1.2em\">" . esc_html__('Plugins for Wordpress','next-tiny-date') . "</a></b></em></p>";
  echo "<p><em><b>" . esc_html__('You like this plugin?','next-tiny-db') . " <a target=\"_blank\" href=\"https://www.paypal.com/donate/?hosted_button_id=" . NTDB_KEY_DONATE . "\" style=\"color:#FE5500;font-weight:bold;font-size:1.2em\">" . esc_html__('Offer me a coffee!','next-tiny-db') . "</a></b></em>";
  $CoffeePath = plugin_dir_url( dirname( __FILE__ ) )  . '/images/coffee-donate.gif';
  echo '&nbsp;<img src="' . esc_attr($CoffeePath) . '"></p>';
  print '</div>';
}

add_action("admin_enqueue_scripts", "ntdb_add_script_upload");
function ntdb_add_script_upload()
{	wp_enqueue_media();
  wp_register_script('ntdb_upload', plugins_url('/',__DIR__).'js/ntdb_upload.js', array('jquery'), '1', true );
  wp_enqueue_script('ntdb_upload');
}

function ntdb_GetSlug($parStr)
{ $strSlug = strtolower($parStr);
  return str_replace(" ","-",$strSlug);
}

function ntdb_display($atts)
{ global $gCommonPath;

  $atts = shortcode_atts(
        array(
          'db_name' => 'tiny_db',
          'title' => 'Tiny DB',
          'key' => 1,
          'table_results' => false,
          'form_show' => true,
          'max_results' => 30
          ), $atts, 'next_tiny_db' );
  $tmpIdBD = esc_html($atts['db_name']);
  $tmpIdBD = ntdb_GetSlug($tmpIdBD);
  $tmpPos = strpos($tmpIdBD,".csv"); if ($tmpPos) $tmpIdBD = substr($tmpIdBD,0,$tmpPos);
  $tmpTitle = esc_html($atts['title']);
  $tmpKey = esc_html($atts['key']);
  $strTableResults = esc_html($atts['table_results']);
  $strFormShow = esc_html($atts['form_show']);
  $NbMaxResults = esc_html($atts['max_results']);
  
  $FormShow = true;
  $strFormShow = strtolower($strFormShow);
  if (($strFormShow=="0")or($strFormShow=="no")or($strFormShow=="false")) $FormShow = false;
  
  $tmpOptFileNameBD = "optPathFileBD_" . $tmpIdBD;
  $PathBD = get_option($tmpOptFileNameBD);
  
  $TableResults = false;
  $strTableResults = strtolower($strTableResults);
  if (($strTableResults=="1")or($strTableResults=="yes")or($strTableResults=="true")) $TableResults = true;

  $NumLines = 0; $NbRows = 0;
  $lineHeaders = "";
  $tmpOpt = "optIsTitle_" . $tmpIdBD;        $opt_IsTitle = get_option($tmpOpt);
  $tmpOpt = "optFieldsSeparator_" . $tmpIdBD; $opt_FieldsSeparator = get_option($tmpOpt); if (!$opt_FieldsSeparator) $opt_FieldsSeparator = 1;
  $ntdb_FieldsChar = ($opt_FieldsSeparator==1?";":",");
  $tmpOpt = "optSearchField_" . $tmpIdBD;    $opt_SearchField = get_option($tmpOpt);  if (!$opt_SearchField) $opt_SearchField = 2;
  $tmpOpt = "optPartSearch_" . $tmpIdBD;     $opt_PartSearch = get_option($tmpOpt);
  $tmpOpt = "optCaseSearch_" . $tmpIdBD;     $opt_CaseSearch = get_option($tmpOpt);
  $tmpOpt = "optShowHead_" . $tmpIdBD;       $opt_ShowHead = get_option($tmpOpt);
  $tmpOpt = "optLineSpace_" . $tmpIdBD;      $opt_LineSpace = get_option($tmpOpt);
  $tmpOpt = "optImgPos_" . $tmpIdBD;         $opt_ImgPos = get_option($tmpOpt); if (!$opt_ImgPos) $opt_ImgPos = "left";
  $tmpOpt = "optImgWidth_" . $tmpIdBD;       $opt_ImgWidth = get_option($tmpOpt); if (!$opt_ImgWidth) $opt_ImgWidth = "35";
  $tmpOpt = "optNewPage_" . $tmpIdBD;        $opt_NewPage = get_option($tmpOpt);

  if (file_exists($PathBD))
     { $fd = fopen($PathBD,"r");
       while(!feof($fd))
            { $line = fgets($fd);
              if (trim($line) != "")
                 { $NumLines++;
                   $listLine = explode($ntdb_FieldsChar,$line);
          
                   if ($NumLines == 1)
                      { $tmpIndiceKey = $tmpKey;
                        $NbRows = count($listLine);
                        if ($opt_IsTitle)
                           { $tmpStartLine = 0;
                             for ($i=1;$i<=$NbRows;$i++)
                                 { $A_TinyHeader[$i] = esc_attr(trim(utf8_encode($listLine[$i-1])));
                                   if (!is_numeric($tmpKey))
                                      { if ($A_TinyHeader[$i] == $tmpKey) $tmpIndiceKey = $i; 
                                      }
                                   $A_TinyHeader_slug[$i] = strtolower($A_TinyHeader[$i]);
                                   $A_TinyHeader_slug[$i] = str_replace(" ","-",$A_TinyHeader_slug[$i]);
                                 }
                           }
                        else
                           { $tmpStartLine = 1;
                             for ($i=1;$i<=$NbRows;$i++)
                                 { $A_TinyHeader[$i] = "Col " . $i;
                                   if (!is_numeric($tmpKey))
                                      { if ($A_TinyHeader[$i] == $tmpKey) $tmpIndiceKey = $i; 
                                      }
                                   $A_TinyHeader_slug[$i] = "col-" . $i;
                                   $A_TinyDB[$tmpStartLine][$i] = esc_attr(trim($listLine[$i-1])); 
                                 } 
                           }
                        if ($tmpIndiceKey == 0) 
                           $tmpKey = 1;
                        else
                           $tmpKey = $tmpIndiceKey;
                      }
                   else   
                      { $tmpStartLine++;
                        for ($i=1;$i<=$NbRows;$i++)
                            { $A_TinyDB[$tmpStartLine][$i] = esc_attr(trim(utf8_encode($listLine[$i-1])));
                            }
                      }
                 }
            }
       if ($opt_IsTitle) $NumLines--;
       fclose($fd);
     }
  else
     echo '<b><font color="ff000">' . esc_attr(ucfirst($tmpIdBD)) . '</font></b><font color="#ff0000"> : ' . esc_html__('Tiny DB not found!','next-tiny-db') . '</font><br><br>';

  for($i=1;$i<=$NbRows;$i++)
     { $tmpOpt = 'optRow_' . $tmpIdBD . '_' . $i;
       ${'opt_Row_'.$i} = get_option($tmpOpt);
       $tmpOpt = 'optRowBold_' . $tmpIdBD . '_' . $i;
       ${'opt_RowBold_'.$i} = get_option($tmpOpt);
       $tmpOpt = 'optRowEm_' . $tmpIdBD . '_' . $i;
       ${'opt_RowEm_'.$i} = get_option($tmpOpt);
       $tmpOpt = 'optRowHidden_' . $tmpIdBD . '_' . $i;
       ${'opt_RowHidden_'.$i} = get_option($tmpOpt);
     }
     
  $tmpSearch = sanitize_text_field($_GET['tinysearch']);
  if (($tmpKey < 1) or ($tmpKey > $NbRows)) $tmpKey = 1;
  if ($tmpTitle) echo "<h3 class=\"widgettitle\">" . esc_attr($tmpTitle) . " [" . esc_attr(ucfirst($tmpIdBD)) . "]</h3>";

  $tmpSiteURL = site_url();
  $tmpFoundStar = false;
  $g = 0;
  $tmpInOptionGroup = false;
  if ($FormShow)
     { echo '<form type="get" action="">';
       if ($opt_SearchField == 1)
          { echo '<b>' . esc_attr($A_TinyHeader[$tmpKey]) . ' </b><input type="text" name="tinysearch" />';
          }
       else
          { $tmpNbItems = 0;
            echo '<b>' . esc_attr($A_TinyHeader[$tmpKey]) . ' </b><select name="tinysearch" />';
            for($i=1;$i<=$NumLines;$i++)
               { if (trim($A_TinyDB[$i][$tmpKey]) != "")
                    { if(!$opt_CaseSearch)
                        { if (substr($A_TinyDB[$i][$tmpKey],0,1) == '*')
                             { $tmpStr = substr($A_TinyDB[$i][$tmpKey],1);
                               $A_TinyDB[$i][$tmpKey] = '*' . ntdb_tolower(utf8_decode($tmpStr));
                             }
                          else
                             $A_TinyDB[$i][$tmpKey] = ntdb_tolower(utf8_decode($A_TinyDB[$i][$tmpKey]));
                        }
                      if (!in_array($A_TinyDB[$i][$tmpKey], $tmpA_Items))
                         { $tmpA_Items[$tmpNbItems] = $A_TinyDB[$i][$tmpKey];
                           $tmpNbItems++;
                         }
                      if (substr($A_TinyDB[$i][$tmpKey],0,1) == '*') $tmpFoundStar = true;
                    }
               }

            if (!$tmpFoundStar)
               { sort($tmpA_Items);
                 for($i=0;$i<$tmpNbItems;$i++)
                    { echo '<option ' . ($tmpSearch==$tmpA_Items[$i]?"selected ":"") . 'value="' . esc_attr($tmpA_Items[$i]) . '">' . esc_attr($tmpA_Items[$i]) . '</option>';
                    }
               }
            else
               { for($i=0;$i<$tmpNbItems;$i++)
                    { if (substr($tmpA_Items[$i],0,1) == '*') 
                         { if (!empty($tmpA_Group))
                              { sort($tmpA_Group);
                                for($s=0;$s<$g;$s++)
                                    echo '<option ' . ($tmpSearch==$tmpA_Group[$s]?"selected ":"") . 'value="' . esc_attr($tmpA_Group[$s]) . '">' . esc_attr($tmpA_Group[$s]) . '</option>';
                                unset($tmpA_Group);
                                if ($tmpInOptionGroup) echo '</optgroup>'; 
                              }
                           $tmpInOptionGroup = true;
                           $g = 0;
                           echo '<optgroup label="' . esc_attr(substr($tmpA_Items[$i],1)) . '">';
                         }
                      else
                         { $tmpA_Group[$g] = $tmpA_Items[$i];
                           $g++;
                         }
                    }

                 if ($tmpInOptionGroup == true)
                    { sort($tmpA_Group);
                      for($s=0;$s<$g;$s++)
                         echo '<option ' . ($tmpSearch==$tmpA_Group[$s]?"selected ":"") . 'value="' . esc_attr($tmpA_Group[$s]) . '">' . esc_attr($tmpA_Group[$s]) . '</option>';
                      unset($tmpA_Group);
                      echo '</optgroup>'; 
                    }
               }
            echo '</select>';
          }
       echo '<input type="submit" value="' . esc_html__('Search','next-tiny-db') . '" />';
       echo '</form>';
     }

  if ($tmpSearch != "")
     { if(!$opt_CaseSearch) $tmpSearch = strtoupper($tmpSearch);

       if ($TableResults)
          { echo '<table style="display: block; overflow-x: auto; XXXwhite-space: nowrap;">';
            if ($opt_ShowHead)
               { echo '<tr>';
                 for ($j=1;$j<=$NbRows;$j++)
                     { $tmpHidden = get_option('optRowHidden_' . $tmpIdBD . '_' . $j);
                       if (!$tmpHidden)
                          echo "<th>" . esc_attr(ntdb_tolower(utf8_decode($A_TinyHeader[$j]))) . "</th>";
                     }
                 echo '</tr>';
               }
          }
                    
       $tmpNbResults = 0;
       $tmpFound = false;
       $tmpDataPath = $gCommonPath . "/NTDB/" . $tmpIdBD . "/";
       for($i=1;$i<=$NumLines;$i++)
          { $Haystack = (!$opt_CaseSearch)?strtoupper($A_TinyDB[$i][$tmpKey]):$A_TinyDB[$i][$tmpKey];
            if (!$opt_PartSearch)
               { $tmpFound = ($Haystack === $tmpSearch);
               }
            else
               { $tmpPos= strpos($Haystack,$tmpSearch);
                 $tmpFound = ($tmpPos !== false);
               }
 
            if ($tmpFound)
               { $tmpNbResults++;
                 
                 if (!$TableResults)
                    { $tmpDataDesc = $A_TinyDB[$i][$tmpKey];
                      $tmpDataDesc = strtolower($tmpDataDesc);
                      $tmpDataDesc = str_replace(" ","-",$tmpDataDesc);
                      $tmpImgFile =  $tmpDataDesc . ".jpg";
                      if (!file_exists($tmpDataPath . $tmpImgFile)) $tmpImgFile = $tmpDataDesc . ".png";
                      if (file_exists($tmpDataPath . $tmpImgFile)) 
                         { $urlImgFile = plugins_url('/',__DIR__);
                           if (substr($urlImgFile,-1,1) == "/") $urlImgFile = substr($urlImgFile,0,-1);
                           $posSep = strrpos($urlImgFile,"/"); $urlImgFile = substr($urlImgFile,0,$posSep);
                           $posSep = strrpos($urlImgFile,"/"); $urlImgFile = substr($urlImgFile,0,$posSep);
                           $tmpImgFile = $urlImgFile . "/NTDB/" . $tmpIdBD . "/" . $tmpImgFile;
                         }
                      else 
                         $tmpImgFile = "";

                      if ($tmpImgFile != "")
                         { if ($opt_ImgPos == "top") echo '<img src="' . esc_attr($tmpImgFile) . '"><br>';
                           if (($opt_ImgPos == "left") or ($opt_ImgPos == "right")) echo '<table><tr>';
                           if ($opt_ImgPos == "left") echo '<td width="' . esc_attr($opt_ImgWidth) . '%"><img src="' . esc_attr($tmpImgFile) . '"></td>'; //<br>';
                           if (($opt_ImgPos == "left") or ($opt_ImgPos == "right")) echo '<td style="vertical-align: top;">';
                         }
                      $tmpLink = "";
                      for ($j=1;$j<=$NbRows;$j++)
                          { $tmpOptRow = 'optRowHidden_' . $tmpIdBD . '_' . $j; $tmpHidden = get_option($tmpOptRow);
                            if (!$tmpHidden)
                               { if ($opt_ShowHead) echo "<b>" . esc_attr(ntdb_tolower(utf8_decode($A_TinyHeader[$j]))) . "</b> : ";
                     
                     
                                 $tmpA1 = "";$tmpA2 = "";
                                 $tmpOptRow = 'optRowLnk_' . $tmpIdBD . '_' . $j; $tmpPage = get_option($tmpOptRow);
                                 if (($tmpPage != "") and ($j != $tmpKey))
                                    { $tmpLink = $tmpSiteURL . "/" . $tmpPage . "/?tinysearch=" . $A_TinyDB[$i][$j];
                                      $strNewPage = "";
                                      if ($opt_NewPage) $strNewPage = ' target="_' . esc_attr($A_TinyHeader[$j]) . '"';
                                      $tmpA1 = '<a href="' . $tmpLink . '"' . $strNewPage . '>'; $tmpA2 = '</a>';
                                    }
                                 
                                 $tmpOptRow = 'optRow_' . $tmpIdBD . '_' . $j; $tmpColor = get_option($tmpOptRow);
                                 $tmpBold1 = "";$tmpBold2 = "";
                                 $tmpOptRow = 'optRowBold_' . $tmpIdBD . '_' . $j; $tmpBold = get_option($tmpOptRow);
                                 if ($tmpBold)
                                    { $tmpBold1 = "<b>";$tmpBold2 = "</b>"; }
                     
                                 $tmpEm1 = "";$tmpEm2 = "";
                                 $tmpOptRow = 'optRowEm_' . $tmpIdBD . '_' . $j; $tmpEm = get_option($tmpOptRow);
                                 if ($tmpEm)
                                    { $tmpEm1 = "<em>";$tmpEm2 = "</em>"; }

                                 if ($A_TinyDB[$i][$j] != "")
                                    { $strItemW = $A_TinyDB[$i][$j];
                                      if (!$opt_CaseSearch) $strItemW = ntdb_tolower(utf8_decode($strItemW));
                                      echo wp_kses_post($tmpBold1) . wp_kses_post($tmpEm1) . wp_kses_post($tmpA1) . '<font color="' . esc_attr($tmpColor) . '">' . esc_attr($strItemW) . '</font>' . wp_kses_post($tmpA2) . wp_kses_post($tmpEm2) . wp_kses_post($tmpBold2) . '<br>';
                                    }
                                 else
                                    { $tmpDataDesc = $A_TinyDB[$i][$tmpKey] . ".txt";
                                      $tmpDataDesc = strtolower($tmpDataDesc);
                                      $tmpDataDesc = str_replace(" ","-",$tmpDataDesc);
                                 
                                      $tmpDataFilePath = $gCommonPath . "/NTDB/" . $tmpIdBD . "/" . $A_TinyHeader_slug[$j] . "/" . $tmpDataDesc;  
                                      if (file_exists($tmpDataFilePath))
                                         { $fd = fopen($tmpDataFilePath,"r");
                                           while(!feof($fd))
                                                { $line = fgets($fd);
                                                  echo wp_kses_post($line);
                                                }
                                           fclose($fd);
                                           }
                                    }
                                 if ($opt_LineSpace) echo "<p>";
                               }
                          }
                      if ($tmpImgFile != "")
                         { if (($opt_ImgPos == "left") or ($opt_ImgPos == "right")) echo '</td>';
                           if ($opt_ImgPos == "right") echo '<td width="' . esc_attr($opt_ImgWidth) . '%"><img src="' . esc_attr($tmpImgFile) . '"></td>';
                           if (($opt_ImgPos == "left") or ($opt_ImgPos == "right")) echo '</tr></table>';
                           if ($opt_ImgPos == "bottom") echo '<img src="' . esc_attr($tmpImgFile) . '"><br>';
                         }
                    }
                 else
                    { echo '<tr>';
                      for ($j=1;$j<=$NbRows;$j++)
                          { $tmpOptRow = 'optRowHidden_' . $tmpIdBD . '_' . $j; $tmpHidden = get_option($tmpOptRow);
                            if (!$tmpHidden)
                               { $tmpOptRow = 'optRow_' . $tmpIdBD . '_' . $j; $tmpColor = get_option($tmpOptRow);
                                 
                                 $tmpA1 = "";$tmpA2 = "";
                                 $tmpOptRow = 'optRowLnk_' . $tmpIdBD . '_' . $j; $tmpPage = get_option($tmpOptRow);
                                 if (($tmpPage != "") and ($j != $tmpKey))
                                    { $tmpLink = $tmpSiteURL . "/" . $tmpPage . "/?tinysearch=" . $A_TinyDB[$i][$j];
                                      $strNewPage = "";
                                      if ($opt_NewPage) $strNewPage = ' target="_' . esc_attr($A_TinyHeader[$j]) . '"';
                                      $tmpA1 = '<a href="' . $tmpLink . '"' . $strNewPage . '>'; $tmpA2 = '</a>'; 
                                    }
                                 $tmpBold1 = "";$tmpBold2 = "";
                                 $tmpOptRow = 'optRowBold_' . $tmpIdBD . '_' . $j; $tmpBold = get_option($tmpOptRow);
                                 if ($tmpBold)
                                    { $tmpBold1 = "<b>";$tmpBold2 = "</b>"; }
                     
                                 $tmpEm1 = "";$tmpEm2 = "";
                                 $tmpOptRow = 'optRowEm_' . $tmpIdBD . '_' . $j; $tmpEm = get_option($tmpOptRow);
                                 if ($tmpEm)
                                    { $tmpEm1 = "<em>";$tmpEm2 = "</em>"; }

                                 if ($A_TinyDB[$i][$j] != "")
                                    { $strItemW = $A_TinyDB[$i][$j];
                                      if (!$opt_CaseSearch) $strItemW = ntdb_tolower(utf8_decode($strItemW));
                                      echo '<td>' . wp_kses_post($tmpBold1) . wp_kses_post($tmpEm1) . wp_kses_post($tmpA1) . '<font color="' . esc_attr($tmpColor) . '">' . esc_attr($strItemW) . '</font>' . wp_kses_post($tmpA2) . wp_kses_post($tmpEm2) . wp_kses_post($tmpBold2) . '</td>';
                                    }
                                 else
                                    { $tmpDataDesc = $A_TinyDB[$i][$tmpKey] . ".txt";
                                      $tmpDataDesc = strtolower($tmpDataDesc);
                                      $tmpDataDesc = str_replace(" ","-",$tmpDataDesc);
                                 
                                      $tmpDataFilePath = $gCommonPath . "/NTDB/" . $tmpIdBD . "/" . $A_TinyHeader_slug[$j] . "/" . $tmpDataDesc;  
                                      if (file_exists($tmpDataFilePath))
                                         { echo '<td>';
                                           $fd = fopen($tmpDataFilePath,"r");
                                           while(!feof($fd))
                                                { $line = fgets($fd);
                                                  echo wp_kses_post($line);
                                                  echo '<br>';
                                                }
                                           fclose($fd);
                                           echo '</td>';
                                         }
                                    }
                               }
                          }
                      echo "</tr>";
                      if ($tmpNbResults >= $NbMaxResults) break;
                    }
               }
          }
       echo '</table>';
       switch ($tmpNbResults)
              { case 0: esc_html_e('No result found!','next-tiny-db'); break;
                case 1: echo esc_attr($tmpNbResults) . ' ';
                        esc_html_e('result found.','next-tiny-db'); break;
                default:echo esc_attr($tmpNbResults) . ' ';
                        esc_html_e('results found.','next-tiny-db'); break;

              }
     }
} 
add_shortcode('next_tiny_db','ntdb_display');