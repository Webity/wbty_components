<?php
/**
 * @copyright	Copyright (C) 2013 Webity. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Preloader for Wbty_components
 *
 * @package		Webity.Wbty_components
 * @subpackage	System.wbty_components
 */
class WbtyJhtml
{
	static public function register()
	{
		$methods = get_class_methods('WbtyJhtml');

		foreach ($methods as $method) {
			if ($method != 'register') {
				JHtml::register('wbty.'.$method, array('WbtyJhtml', $method));
			}
		}

		return true;
	}

	public static function buildEditForm($form, $hidden = true, $div_class = 'hidden-form') {
		if (!$form instanceof JForm) {
			return false;
		}

		ob_start();
		foreach ($form->getFieldsets() as $fieldset) {
			echo '<fieldset name="'.$fieldset->name.'"';
			$class = array();
			$field = $value = '';
			if ($fieldset->multiple) {
				 $class[] = 'multiple';
			}
			if ($fieldset->dependency) {
				$class[] = 'dependency';
				$field = $fieldset->field;
				$value = $fieldset->value;
			}
			if ($class) {
				echo ' class="'. implode(' ', $class) . '"';
			}
			if ($field && $value) {
				echo ' data-field="'. $field . '" data-value="'. $value . '"';
			}
			if ($fieldset->copy) {
				echo ' data-copy="' . $fieldset->copy . '"';
			}
			echo '>';
			if ($fieldset->legend) {
				echo '<legend>'.$fieldset->legend.'</legend>';
			}
			if ($fieldset->soc) {
				echo '<p>This section should have a search or create option. Only one is currently shown.</p>';
			}
			//echo '<div class="edit-values">';
			foreach($form->getFieldset($fieldset->name) as $field):
				if (!$field->hidden && $field->display_value) {
				//	echo strip_tags($field->label) . ': <span class="' . str_replace(array('[',']'), array('_'),$field->name) . '">' . $field->value . '</span><br>';
				}
			endforeach;
			echo '<!--</div>-->
			<div class="edit-form">';
			foreach($form->getFieldset($fieldset->name) as $field):
				// If the field is hidden, only use the input.
				if ($field->hidden):
					echo $field->input;
				elseif (strtolower($field->type) == 'editor'):
        		?>
		        <div class="control-group">
		            <?php echo $field->label; ?>
					<?php echo $field->input; ?>
		        </div>
		        <?php
				else:
				?>
				<div class="control-group">
					<?php echo str_replace('<label', '<label class="control-label"', $field->label); ?>
					<div class="controls">
						<?php echo $field->input; ?>
					</div>
				</div>
				<?php
				endif;
			endforeach;
			echo '</div>';
			echo '</fieldset>';
		}
		$html = ob_get_contents();
		ob_end_clean();

		if ($hidden) {
			$html = '<div style="display:none;" class="'.$div_class.'">'.$html.'</div>';
		}

		return $html;
	}

	static public function renderField($field) {
		if (!$field) {
			return;
		}

		// If the field is hidden, only use the input.
        if ($field->hidden):
            echo $field->input;
        elseif (strtolower($field->type) == 'editor'):
        	?>
        <div class="control-group">
            <?php echo $field->label; ?>
			<?php echo $field->input; ?>
        </div>
        <?php
        else:
        ?>
        <div class="control-group">
            <?php echo str_replace('<label', '<label class="control-label"', $field->label); ?>
            <div class="controls">
                <?php echo $field->input; ?>
            </div>
        </div>
        <?php
        endif;

		return true;
	}

	/*
	 * Resizes all youtube, vimeo, ect. videos as the site resizes (based on aspect ratio)
	 * @container		(string)	A class, ID, or element that surrounds the videos that should be resized
	 */
	static public function videoresizer($container = 'body') {
		static $setup;

		if ($setup) {
			return;
		}
		
		JHtml::script('wbty_components/jquery.fitvids.js', false, true);
		
		ob_start();
		?>
        
        jQuery(function ($) {
			$("<?php echo $container; ?>").fitVids();
		});
        
		<?php
		$script = ob_get_contents();
		ob_end_clean();

		JFactory::getDocument()->addScriptDeclaration($script);

		$setup = true;

		return true;
	}
	
	/*
	 * Adds support to convert tables to still-readable formats once a site breaks below 767px
	 * @retainStructure		(string)	A class or ID that will not be converted once the 767px break is hit
	 */
	static public function responsivetables($retainStructure = '.statictable') {
		static $setup;

		if ($setup) {
			return;
		}
		
		JHtml::script('wbty_components/stacktable.js', false, true);
		
		ob_start();
		?>
        
        jQuery(function ($) {
			$('table:not(<?php echo $retainStructure; ?>, .category-list table)').stacktable();
		});
        
		<?php
		$script = ob_get_contents();
		ob_end_clean();
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration($script);
		
		ob_start();
		?>
        
        .stacktable {display: none;}

        @media (max-width: 767px) {
            table {display: none;}
            .stacktable, .recaptchatable, .category-list table {display: table;}
            <?php echo $retainStructure; ?> {display: table; width: 100%;}
            <?php echo $retainStructure; ?> td {display: block; width: auto !important;}
        }

		<?php $style = ob_get_contents();
		ob_end_clean();
		
		$document->addStyleDeclaration($style);
		
		$setup = true;

		return true;
	}

	static function fileuploaderscripts() {
		JHtml::script('wbty_components/fileuploader.js', false, true);
		JHtml::stylesheet('wbty_components/fileuploader.css', false, true);
	}

	static function fileuploader($action = 'index.php', $callback='wbtyComponentsProcessImageUpload', $ajax = false) {
		static $id;

		if (!$id) {
			$id = 1;
		} else {
			$id++;
		}

		JHtml::_('wbty.fileuploaderscripts');

		ob_start(); ?>
<div id="file-uploader-<?php echo $id; ?>">		
    <noscript>			
        <p>Please enable JavaScript to use the bulk file uploader.</p>
    </noscript>         
</div>

<div class="qq-upload-extra-drop-area">Drop files here too</div>
		<?php
		$output = ob_get_clean();
		// print output for the place that this is called
		echo $output;

		// setup javascript
		ob_start(); ?>
	function createUploader(){            
        var uploader = new qq.FileUploader({
            element: document.getElementById('file-uploader-<?php echo $id; ?>'),
            action: '<?php echo JRoute::_($action, false); ?>',
            debug: true,
			sizelimit: 10000000,
			uploadButtonText: 'Click or drag here to upload files',
			failUploadText: 'Upload failed',
            extraDropzones: [qq.getByClass(document, 'qq-upload-extra-drop-area')[0]],
			onComplete: function (id, filename, responseJSON) {
				if (responseJSON['success'] === true) {
					<?php echo $callback; ?>(id, filename, responseJSON);
				}
			}
        });           
    }
    <?php if ($ajax) : ?>
    	createUploader();
	<?php else : ?>
    	window.onload = createUploader; 
	<?php endif; ?>

		<?php
		$javascript = ob_get_clean();

		if ($ajax) {
			echo '<script>' . $javascript . '</script>';
		} else {
			JFactory::getDocument()->addScriptDeclaration($javascript);
		}

		return true;
	}
	
	/**
	 * Match the heights between several elements (http://brm.io/jquery-match-height/)
	 * 
	 * @param	$selector		string		A CSS selector for the items to be matched
	 */
	static function equalHeight($selector = '.rt-container .equal-height') {
			
		JHtml::_('script', 'wbty_components/jquery.matchHeight-min.js', false, true);

		ob_start();
		?>

		jQuery(window).load(function() {
			jQuery('<?php echo $selector; ?>').matchHeight();
		});

		<?php 
		$script = ob_get_clean();

		JFactory::getDocument()->addScriptDeclaration($script);

		return true;
	}

	static function hoverSwap() {
		static $setup;

		if ($setup) {
			return;
		}

		ob_start();
		?>

jQuery(document).ready(function($) {
	jQuery('img[data-hover-src]').hover(function() {
		$(this).data('src', $(this).attr('src'));
		$(this).attr('src', $(this).attr('data-hover-src'));
	}, function () {
		$(this).attr('src', $(this).data('src'));
	})
});

		<?php
		$output = ob_get_clean();

		JFactory::getDocument()->addScriptDeclaration($output);

		$setup = true;

		return true;
	}

	static function jsBase() {
		static $setup;

		if ($setup) {
			return;
		}

		JFactory::getDocument()->addScriptDeclaration('window.juri_root = \''.JURI::root(true).'/\'; window.juri_base = \''.JURI::base(true).'/\';');

		$setup = true;

		return true;
	}

	static function base() {
		static $setup;

		if ($setup) {
			return;
		}


		$setup = true;

		return true;
	}
}
