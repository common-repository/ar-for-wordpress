
function ar_open_tab(evt, tabName, target) {
  // Declare all variables
  var i, ar_tabcontent, ar_tablinks;
  // Get all elements with class="tabcontent" and hide them
  ar_tabcontent = document.getElementsByClassName("ar_tabcontent");
  for (i = 0; i < ar_tabcontent.length; i++) {
    ar_tabcontent[i].style.display = "none";
  }
  var ar_option = document.getElementById("ar_admin_options");
  if (ar_option) {
    ar_option.style.display = "none";
  }
  var ar_modelviewer = document.getElementById("ar_admin_modelviewer");
  if (ar_modelviewer) {
    ar_modelviewer.style.display = "none";
  }
  
  // Get all elements with class="ar_tablinks" and remove the class "active"
  ar_tablinks = document.getElementsByClassName("ar_tablinks");
  for (i = 0; i < ar_tablinks.length; i++) {
    ar_tablinks[i].className = ar_tablinks[i].className.replace(" active", "");
  }
var tabElement = document.getElementById(tabName);
  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  //evt.currentTarget.className += " active";
  document.getElementById(target).className += " active";
  
if (tabElement && (tabElement.id.startsWith("model_files_content") || tabElement.id.startsWith("asset_builder_content"))) {
  if (ar_option) {
    ar_option.style.display = "block";
  }
  if (ar_modelviewer) {
    ar_modelviewer.style.display = "block";
  }
}
}

function ar_activeclass(divId) {
  var element = document.getElementById(divId);
  if (element) {
    element.className += " active";
  }
}



jQuery(document).ready(function(){
    
    //Accordian Content
    jQuery('#ar_wp_advance_custom_attachment').on('click','.ar_accordian', function(event){
                    
        jQuery(this).toggleClass("ar_active");

        var panel = jQuery(this).next();
        //console.log(panel[0].scrollHeight);
        /*if (panel.style.display === "block") {
          panel.style.display = "none";
        } else {
          panel.style.display = "block";
        }*/
        if (panel[0].style.maxHeight) {
          panel[0].style.maxHeight = null;
        } else {
          panel[0].style.maxHeight = panel[0].scrollHeight + "px";
        }
    }); 
    
    jQuery('#licence_page, #licence_page2').on('click','.ar_accordian', function(event){
                   
        jQuery(this).toggleClass("ar_active");

        var panel = jQuery(this).next();
        //console.log(panel[0].scrollHeight);
        /*if (panel.style.display === "block") {
          panel.style.display = "none";
        } else {
          panel.style.display = "block";
        }*/
        if (panel[0].style.maxHeight) {
          panel[0].style.maxHeight = null;
        } else {
          panel[0].style.maxHeight = panel[0].scrollHeight + "px";
        }
    }); 
    jQuery(document).on('click','#toggle-model-fields', function(event){
        event.preventDefault();
        //console.log(jQuery(this).data('status'));
        if(jQuery(this).data('status') == 'hidden'){
            jQuery('#_usdz_file').attr('type','text');
            jQuery('#_glb_file').attr('type','text');
            jQuery(this).data('status','visible');
            jQuery(this).text('Hide Model Fields');
        } else {
            jQuery('#_usdz_file').attr('type','hidden');
            jQuery('#_glb_file').attr('type','hidden');
            jQuery(this).data('status','hidden');
            jQuery(this).text('Show Model Fields');
        }

    });
});  

//

        

//

function ARModelFields(containerId, options) {
    // Default options to an empty object if not provided
    options = options || {};

    // Initialize instance properties
    this.modelid = containerId;
    this.options = Object.assign({}, options);  // Object.assign is ES6, so replaced with manual copying in ES5
    this.suffix = '';
    this.modelViewer = null;

    // Initialize AR Model Fields
    this.initARModelFields();
}

// Add methods to the prototype
ARModelFields.prototype.initARModelFields = function() {
    this.uploadButtonScript();
    this.animationSelector();
    this.hotSpot();
    this.cameraRotation();
    this.assetBuilder();

    if (this.options.wc_model === '0') {
        this.modelFields();
        // console.log('arwp');
    } else {
        // console.log('arwc');
    }
};

ARModelFields.prototype.uploadButtonScript = function() {
    var options = this.options;
    jQuery(function($) {    
        var custom_uploader;
        var button_clicked;

        
        $(document).on('click','.upload_usdz_button, .upload_glb_button, .upload_skybox_button, .upload_environment_button, .upload_qr_image_button, .upload_asset_texture_button, .upload_asset_texture_button_0, .upload_asset_texture_button_1, .upload_asset_texture_button_2, .upload_asset_texture_button_3, .upload_asset_texture_button_4, .upload_asset_texture_button_5, .upload_asset_texture_button_6, .upload_asset_texture_button_7, .upload_asset_texture_button_8, .upload_asset_texture_button_9', function(e) {
            window.button_clicked = $(this).attr('class');
            e.preventDefault();

            var variation_id = '';
            var model_idd = '';
            var suffix = '';

            //console.log('buttonclicked');

            if(e.target.hasAttribute('data-variation')){
                variation_id = '_var_' + e.target.getAttribute('data-variation');
                model_idd = e.target.getAttribute('data-variation');
                suffix = variation_id;
            } else {
                model_idd = options.product_parent;
            }

            $('#uploader_modelid').val(model_idd);
        
            //If the uploader object has already been created, reopen the dialog
            if (custom_uploader) {
                custom_uploader.open();
                return;
            }
    
            //Extend the wp.media object
            custom_uploader = wp.media.frames.file_frame = wp.media({
                title: options.uploader_title,
                button: {
                    text: options.uploader_title
                },
                multiple: true
            });
    
            //When a file is selected, grab the URL and set it as the text field value
            custom_uploader.on('select', function() {
                var attachments = custom_uploader.state().get('selection').map( 
                   function( attachment ) {
                       attachment.toJSON();
                       return attachment;
                  });

                  //console.log(window.button_clicked);

                 $.each(attachments, function( index, attachement ) {
                      
                    var fileurl=attachments[index].attributes.url;
                    var filetype = fileurl.substring(fileurl.length - 4, fileurl.length).toLowerCase();
                    var modl_id = $('#uploader_modelid').val();
                    var sffx = '';

                    if(modl_id != options.product_parent){
                        sffx = '_var_' + modl_id;
                    }
                    //.reality files = lity (last 4 chars)
                    if ((filetype === 'usdz') || (filetype === 'USDZ') || (filetype === 'lity') || (filetype === 'LITY')){
                        
                        $('#_usdz_file' + sffx).attr('value',fileurl);
                        var usdz_filename = fileurl.substring(fileurl.lastIndexOf('/') + 1);
                        document.getElementById("usdz_filename"  + sffx).innerHTML= usdz_filename;
                        document.getElementById("usdz_thumb_img").src = options.usdz_thumb;
                        document.getElementById('usdz_thumb_img').classList.add('ar_file_icons_pulse');

                    }else if ((filetype === '.glb')||(filetype === 'gltf')||(filetype === '.zip')||(filetype === '.dae')){
                        
                        $('#_glb_file'  + sffx).attr('value',fileurl);
                        var glb_filename = fileurl.substring(fileurl.lastIndexOf('/') + 1);
                        document.getElementById("glb_filename"  + sffx).innerHTML=glb_filename;
                        document.getElementById("glb_thumb_img").src = options.glb_thumb;
                        var element = document.getElementById("model_" + modl_id);
                        
                        var element2 = document.getElementById("ar_admin_model_" + modl_id);
                        if (element2) {
                            element2.style.display = "block";
                        }
                        //console.log("model_" + fileurl);
                        //console.log(sffx);
                        element.setAttribute("src", fileurl);


                    } else if ((filetype === '.hdr') || (filetype === '.jpg') || (filetype === '.png')){

                        if (window.button_clicked.indexOf('upload_skybox_button') != -1){
                            //console.log("skybox clicked");
                            $('#_skybox_file' + sffx).val(fileurl).trigger('change'); 
                            //$(\'#_skybox_file\' + sffx).trigger(\'change\'); 
                        }
                        else if (window.button_clicked.indexOf('upload_environment_button') != -1){
                            //console.log("envi clicked");
                            $('#_ar_environment' + sffx).val(fileurl).trigger('change');
                            //$(\'#_ar_environment\' + sffx).trigger(\'change\');  
                        }
                        else if (window.button_clicked.indexOf('upload_qr_image_button') != -1){
                            $('#_ar_qr_image' + sffx).val(fileurl); 
                            $('#_ar_qr_image' + sffx).trigger('change'); 
                        }

                        //console.log(window.button_clicked);
                        
                        //Asset Builder Textures
                        $( ".upload_asset_texture_button" ).each(function( index ) {
                            if (window.button_clicked.indexOf('upload_asset_texture_button_' + index) != -1){
                                $('#_asset_texture_file_' + index).val(fileurl).trigger('input');
                                if ($('#_ar_asset_file').val()) {
                                    $('#ar_asset_builder_submit_container').css('display', 'block');
                                }
                                $('#ar_asset_builder_texture_done').html('&#10003;');
                            }
                        });
                    }
                        
                        
                    else{
                       
                
                            //$js_alert =__('Invalid file type. Please choose a USDZ, REALITY, GLB, GLTF, ZIP, HDR, JPG, PNG, DAE, DXF, 3DS, OBJ, PLY or STL file.', $ar_plugin_id );
                            alert(options.js_alert);
                        }

                        $('supports-drag-drop').hide();
                 });
     
            });
            //Open the uploader dialog
            custom_uploader.open();

            e.stopPropagation();
        });  
    });

    /*document.querySelector('._glb_file_field').addEventListener('change', function(e) {
        var model_id = this.getAttribute('data-model');
        var element = document.getElementById("model_" + model_id);
        element.setAttribute("src", this.value);
        var element2 = document.getElementById("ar_admin_model_" + model_id);
        element2.style.display = "block";
    }); */     
    
};


ARModelFields.prototype.animationSelector = function(){
    const modelViewer = document.querySelector('#model_' + this.modelid);
    
    if (!modelViewer) {
        //console.warn('Model viewer not found for model ID:', this.modelid);
        return;
    }
    
    const animationSelector = document.getElementById('_ar_animation_selection' + this.suffix);
    const animationDiv = document.getElementById('animationDiv' + this.modelid);

    if (!animationSelector || !animationDiv) {
        //console.warn('Animation selector or animation div not found for model ID:', this.modelid);
        return;
    }

    // Load the model and retrieve animation names
    modelViewer.addEventListener('load', () => {
        const names = modelViewer.availableAnimations;

        if (names && names.length > 0) {
            names.forEach((animationName, index) => {
                const option = document.createElement('option');
                option.value = animationName;
                option.text = animationName || `Animation ${index + 1}`;
                animationSelector.appendChild(option);
                // Preselect an option if it matches the PHP variable value
                if (animationName === this.ar_animation_selection) {
                    option.selected = true;
                    modelViewer.animationName = animationName;
                }
            });
            // Set the display style to "block" if animations exist
            animationDiv.style.display = 'block';

            // Add event listener to change animations
            animationSelector.addEventListener('change', () => {
                const selectedAnimationName = animationSelector.value;
                modelViewer.animationName = selectedAnimationName;
            });
        }
    });

    const arAnimationCheckbox = document.getElementById('_ar_animation' + this.suffix);
    if (arAnimationCheckbox) {
        arAnimationCheckbox.addEventListener('change', function() {
            const element = document.getElementById("ar-button-animation_");
            if (arAnimationCheckbox.checked) {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        });
    } else {
        //console.warn('Animation checkbox not found for suffix:', this.suffix);
    }
};


ARModelFields.prototype.hotSpot = function(){
    document.body.addEventListener( 'keyup', function ( event ) {
        //Hotspots update on change 
        if( event.target.id.startsWith('_ar_hotspots' )) {
            var hotspot_name = event.target.getAttribute("hotspot_name");
            var hotspot_link = event.target.getAttribute("hotspot_link");
            var match = event.target.id.match(/\[([0-9]+)\]/);
            var index = match ? match[1] : null;
            if (hotspot_name){
                var hotspot_content = document.getElementById(event.target.getAttribute("hotspot_name")).innerHTML;
                // Extract the index from the currentId
                if (index !== null) {
                    // Replace "annotation" with "link" and construct the new id
                    var newId = event.target.id.replace('annotation', 'link');
                    var inputlink = document.getElementById(newId).value;
                }
                var inputtext = event.target.value;
            }
            if (hotspot_link){
                var inputlink = event.target.value;
                // Replace "link" with "annotation" and construct the new id
                var newId = event.target.id.replace('link', 'annotation');
                var inputtext = document.getElementById(newId).value;
                var hotspot_name = hotspot_link;
            }
                
            if (inputlink){
                inputtext = '<a href="'+inputlink+'" target="_blank">'+inputtext+'</a>';
            }
            document.getElementById(hotspot_name).innerHTML='<div class="annotation">'+inputtext+'</div>';
        
        };
        //CTA update on change 
        if( event.target.id=='_ar_cta') {
            document.getElementById("ar-cta-button-container").style="display:block";
            document.getElementById("ar-cta-button").innerHTML=event.target.value;
        };
    });
};


ARModelFields.prototype.cameraRotation = function() {

    // Rotation Limits Compass
    const modelViewer = document.querySelector('#model_' + this.modelid);
    
    if (!modelViewer) {
        //console.warn('Model viewer not found for model ID:', this.modelid);
        return;
    }

    const ar_compass_buttons = document.getElementsByClassName('ar-compass-button');
    const ar_compass_image = document.getElementById('ar-compass-image');
    const ar_rotate_limit = document.getElementById('_ar_rotate_limit');
    
    if (!ar_compass_image || !ar_rotate_limit) {
        //console.warn('Compass image or rotation limit elements not found');
        return;
    }

    ar_rotate_limit.addEventListener('change', function() {
        const min_orbit_arr = modelViewer.getAttribute("min-camera-orbit").split(" ");
        const max_orbit_arr = modelViewer.getAttribute("max-camera-orbit").split(" ");
        const element = document.getElementById("ar_rotation_limits");

        if (!element) {
            //console.warn('Rotation limits element not found');
            return;
        }

        if (ar_rotate_limit.checked) {
            element.style.display = "block";
            element.style.borderColor = "#49848f";
        } else {
            element.style.display = "none";
            modelViewer.setAttribute("min-camera-orbit", 'auto auto ' + min_orbit_arr[2]);
            modelViewer.setAttribute("max-camera-orbit", 'Infinity auto ' + max_orbit_arr[2]);
            document.getElementById("_ar_compass_top_value").value = '';
            document.getElementById("_ar_compass_bottom_value").value = '';
            document.getElementById("_ar_compass_left_value").value = '';
            document.getElementById("_ar_compass_right_value").value = '';
            document.getElementById("ar-compass-top").style.backgroundColor = '#e2e2e2';
            document.getElementById("ar-compass-bottom").style.backgroundColor = '#e2e2e2';
            document.getElementById("ar-compass-left").style.backgroundColor = '#e2e2e2';
            document.getElementById("ar-compass-right").style.backgroundColor = '#e2e2e2';
        }
    });

    // Add a click event listener to each compass button
    if (ar_compass_buttons.length > 0) {
        for (let i = 0; i < ar_compass_buttons.length; i++) {
            ar_compass_buttons[i].addEventListener('mouseenter', function() {
                const id = this.id;
                if (id === 'ar-compass-top') {
                    ar_compass_image.style.transform = 'rotate(0deg)';
                } else if (id === 'ar-compass-bottom') {
                    ar_compass_image.style.transform = 'rotate(180deg)';
                } else if (id === 'ar-compass-right') {
                    ar_compass_image.style.transform = 'rotate(90deg)';
                } else if (id === 'ar-compass-left') {
                    ar_compass_image.style.transform = 'rotate(270deg)';
                }
            });

            ar_compass_buttons[i].addEventListener('click', function() {
                const id = this.id;
                const orbit = modelViewer.getCameraOrbit();
                const min_orbit_arr = modelViewer.getAttribute("min-camera-orbit").split(" ");
                const max_orbit_arr = modelViewer.getAttribute("max-camera-orbit").split(" ");

                let orbitString;
                if (id === 'ar-compass-top') {
                    if (!document.getElementById("_ar_compass_top_value").value) {
                        orbitString = `${orbit.phi}rad`;
                        document.getElementById("_ar_compass_top_value").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    } else {
                        orbitString = 'auto';
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_top_value").value = '';
                    }
                    modelViewer.setAttribute("min-camera-orbit", min_orbit_arr[0] + ' ' + orbitString + ' ' + min_orbit_arr[2]);

                } else if (id === 'ar-compass-bottom') {
                    if (!document.getElementById("_ar_compass_bottom_value").value) {
                        orbitString = `${orbit.phi}rad`;
                        document.getElementById("_ar_compass_bottom_value").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    } else {
                        orbitString = 'auto';
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_bottom_value").value = '';
                    }
                    modelViewer.setAttribute("max-camera-orbit", max_orbit_arr[0] + ' ' + orbitString + ' ' + max_orbit_arr[2]);

                } else if (id === 'ar-compass-right') {
                    if (!document.getElementById("_ar_compass_right_value").value) {
                        orbitString = `${orbit.theta}rad`;
                        document.getElementById("_ar_compass_right_value").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    } else {
                        orbitString = 'Infinity';
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_right_value").value = '';
                    }
                    modelViewer.setAttribute("max-camera-orbit", orbitString + ' ' + max_orbit_arr[1] + ' ' + max_orbit_arr[2]);

                } else if (id === 'ar-compass-left') {
                    if (!document.getElementById("_ar_compass_left_value").value) {
                        orbitString = `${orbit.theta}rad`;
                        document.getElementById("_ar_compass_left_value").value = orbitString;
                        document.getElementById(id).style.backgroundColor = '#f37a23';
                    } else {
                        orbitString = 'auto';
                        document.getElementById(id).style.backgroundColor = '#e2e2e2';
                        document.getElementById("_ar_compass_left_value").value = '';
                    }
                    modelViewer.setAttribute("min-camera-orbit", orbitString + ' ' + min_orbit_arr[1] + ' ' + min_orbit_arr[2]);
                }

                modelViewer.removeAttribute("auto-rotate");
                document.getElementById("_ar_rotate").checked = true;
            });

            modelViewer.addEventListener('camera-change', () => {
                const orbit = modelViewer.getCameraOrbit();
                const orbitString = `${orbit.theta}rad ${orbit.phi}rad ${orbit.radius}m`;

                //console.log(orbitString);

                jQuery(document).on('click','#camera_view_button', function() {
                    //console.log('click: ' + orbitString);
                    document.getElementById("_ar_camera_orbit_set").style.display = 'block';
                    document.getElementById("_ar_camera_orbit").value = orbitString;
                });                        
                
            });
        }
    } else {
        //console.warn('No compass buttons found');
    }
};

ARModelFields.prototype.assetBuilder = function(){
    var asset_JsonList = {"asset_Table" : 
        [
                {"modelMakeID" : "1","modelMake" : "1.0"},
                {"modelMakeID" : "2","modelMake" : "1.4142"},
                {"modelMakeID" : "3","modelMake" : "1.25"},
                {"modelMakeID" : "4","modelMake" : "1.5"},
                {"modelMakeID" : "5","modelMake" : "1.33"}
        ]};
    var modelTypeJsonList = {"1.0" : 
        [
                {"modelTypeID" : "1","modelType" : "100%"},
                {"modelTypeID" : "1.5","modelType" : "150%"},
                {"modelTypeID" : "2","modelType" : "200%"},
                {"modelTypeID" : "2.5","modelType" : "250%"},
                {"modelTypeID" : "3","modelType" : "300%"},
                {"modelTypeID" : "4","modelType" : "400%"},
                {"modelTypeID" : "5","modelType" : "500%"}
        ],
        "1.4142" : 
        [
                {"modelTypeID" : "1","modelType" : "A4 21.0 x 29.7cm / 8.3 x 11.7in"},
                {"modelTypeID" : "1.41","modelType" : "A3 29.7 x 42cm / 11.7 x 16.5in"},
                {"modelTypeID" : "2","modelType" : "A2 42 x 59.4cm / 16.5 x 23.4in"},
                {"modelTypeID" : "2.83","modelType" : "A1 59.4 x 84.1cm / 23.4 x 33.1in"}
        ],
        "1.25" : 
        [
                {"modelTypeID" : "1","modelType" : "20 x 25cm / 8 x 10in"},
                {"modelTypeID" : "1.5","modelType" : "30.5 x 38.0cm / 12 x 15in"},
                {"modelTypeID" : "2","modelType" : "41 x 51cm /16 x 20in"},
                {"modelTypeID" : "3","modelType" : "61 x 76cm / 24 x 30in"}
        ],
        "1.5" : 
        [
                {"modelTypeID" : "1","modelType" : "20 x 30cm"},
                {"modelTypeID" : "1","modelType" : "20 x 30cm / 8 x 12in"},
                {"modelTypeID" : "1.5","modelType" : "30 x 46cm / 12 x 18in"},
                {"modelTypeID" : "2","modelType" : "41 x 51cm / 16 x 24in"},
                {"modelTypeID" : "2.5","modelType" : "51 x 76cm / 20 x 30in"}
        ],
        "1.33" : 
        [
                {"modelTypeID" : "1","modelType" : "23 x 30cm / 9 x 12in"},
                {"modelTypeID" : "1.3","modelType" : "30 x 41cm/ 12 x 16in"},
                {"modelTypeID" : "1.6","modelType" : "38 x 51cm/ 15 x 20in"},
                {"modelTypeID" : "2","modelType" : "46 x 61cm / 18 x 24in"}
        ]
    };
    var ModelListItems= "";
    for (var i = 0; i < asset_JsonList.asset_Table.length; i++){
        ModelListItems+= "<option value='" + asset_JsonList.asset_Table[i].modelMakeID + "'>" + asset_JsonList.asset_Table[i].modelMake + "</option>";
    }
    jQuery("#makeSelectionBox").html(ModelListItems);

    var updatear_asset_size_options = function(ratio) {
        if (ratio === '1') {
            ratio = '1.0';
        }
        //console.log('updating with here ', ratio);
        var listItems = "";
        if (ratio in modelTypeJsonList) {
    
        } else {
    
            ratio = '1.0';
        }
        if (ratio in modelTypeJsonList) {
            for (var i = 0; i < modelTypeJsonList[ratio].length; i++) {
                listItems += "<option value='" + modelTypeJsonList[ratio][i].modelTypeID + "'>" + modelTypeJsonList[ratio][i].modelType + "</option>";
            }
            jQuery("select#ar_asset_size").html(listItems);
            jQuery('#ar_asset_size_container').css('display', 'block');
            /*if (jQuery('#_ar_asset_file').val()) {
                jQuery('#ar_asset_builder_model_done').html('&#10003;');
                jQuery('#ar_asset_builder_submit_container').css('display', 'block');
            }*/
            if (document.getElementById("_asset_texture_file_0").value) {
                var framedValue = document.querySelector("#_ar_framed").value;
                // Get the value of the input field
                var frameColor = document.getElementById("_ar_frame_color").value;
                // Remove the '#' character if it exists
                frameColor = frameColor.replace('#', '');
                var ar_asset_gallery = document.getElementById("_ar_asset_url").value + '?url=' + document.getElementById("_asset_texture_file_0").value + '&ratio=' + document.getElementById("_ar_asset_ratio").value+ '&o=' + document.getElementById("_ar_asset_orientation").value+ '&f=' + framedValue+ '&fc=' + frameColor+ '&opacity=' + document.getElementById("_ar_frame_opacity").value + '&_wpnonce=' + document.getElementById("_ar_wpnonce").value;
                const modelViewer = document.querySelector('#model_'+document.getElementById("_ar_asset_id").value);
                modelViewer.setAttribute("src", ar_asset_gallery);
                jQuery('#_ar_asset_ratio_select').val(document.getElementById("_ar_asset_ratio").value);
                jQuery('#_glb_file').val(ar_asset_gallery);
            }
        }
    }
    jQuery(document).ready(function($) {
        var ratio = $('#_ar_asset_ratio_select').val(); // Or set it to the desired ratio
        // Check if the ratio exists and is valid
        if (ratio) {
            updatear_asset_size_options(ratio); // Call the function with the ratio on page load
            var scale = $('#_ar_x').val();
            if (scale) {
                $('#ar_asset_size').val(scale).change();
            }
        }
    });
    // Light Color - initialize the WordPress color picker
    jQuery('#_ar_light_color',).wpColorPicker({
        palettes: ['#ff0000', '#00ff00', '#0000ff', '#ffffff', '#000000', '#cccccc'],
        change: function(event, ui) {
            // Detect color change and output the selected color
            //console.log('Selected color:', ui.color.toString());
            // Trigger your update function whenever color is changed
            updateModelViewer();
        },

        clear: function() {
            // Handle color clear event (if the user clears the color)
            //console.log('Color cleared');
            updateModelViewer();
        }
        
    });
    
    jQuery(document).ready(function($) {
    
    // Ensure the script runs after the DOM is fully loaded
$(document).ready(function() {
    // Get the current value of the color input field or default to black
    var currentColor = $('#_ar_frame_color').val().trim() || '#000000'; // Default to black if empty
    //console.log('Selected frame color before initialization:', currentColor); // Debug output

    // Check if the color value is correctly set
    if (!currentColor) {
        currentColor = '#000000'; // Fallback to black if still empty, null, or falsy
    }

    // Initialize the color picker with the current color
    $('#_ar_frame_color').wpColorPicker({
        color: currentColor,
        /*palettes: ['#000000', '#00ff00', '#0000ff', '#ffffff', '#000000', '#cccccc'],*/
        change: function(event, ui) {
            // Handle color change event for frame color
            //console.log('Selected frame color:', ui.color.toString());
            updateModelViewer();
        }
    });
    $('#wp-picker-clear').on('click', function() {
        alert('test');
        updateModelViewer();
    });

    // Debugging output after initialization
    //console.log('Color picker initialized with color:', currentColor);
});
    // Function to update the model viewer URL
    function updateModelViewer() {
        if (document.getElementById("_asset_texture_file_0").value) {
            // Check if the _ar_framed checkbox is checked
            var framedValue = document.querySelector("#_ar_framed").value;
            var frameColor = document.getElementById("_ar_frame_color").value;
            // Remove the '#' character if it exists
            frameColor = frameColor.replace('#', '');
            var ar_asset_gallery = document.getElementById("_ar_asset_url").value 
                + '?url=' + document.getElementById("_asset_texture_file_0").value 
                + '&ratio=' + document.getElementById("_ar_asset_ratio").value 
                + '&o=' + document.getElementById("_ar_asset_orientation").value 
                + '&f=' + framedValue  // Assign based on checkbox state
                + '&fc=' + frameColor
                + '&opacity=' + document.getElementById("_ar_frame_opacity").value 
                + '&_wpnonce=' + document.getElementById("_ar_wpnonce").value;

            const modelViewer = document.querySelector('#model_' + document.getElementById("_ar_asset_id").value);
            modelViewer.setAttribute("src", ar_asset_gallery);
            jQuery('#_ar_asset_ratio_select').val(document.getElementById("_ar_asset_ratio").value);
            jQuery('#_glb_file').val(ar_asset_gallery);
        }
    }

    // Event listeners for color and framed fields
    $('#_ar_framed, #_ar_frame_opacity').on('change', function() {
        updateModelViewer();
    });
// Detect manual input in the color field (for users who type the color value)
    $('#_ar_frame_color').on('input', function() {
        // You can trigger the model update or other actions here
        //console.log('Manual color input:', $(this).val());
        updateModelViewer();
    });
    
    

    });
    

    function ar_update_size_fn(){ 
        var ratio = jQuery('#_ar_asset_ratio').val();
        alert (ratio);
        if (ratio === '1') {
            ratio = '1.0';
        }
        jQuery('#_ar_asset_ratio_select').val(ratio);
        // Remove " Matches your Image" from all options
        jQuery('#_ar_asset_ratio_select option:not([value="' + ratio + '"])').each(function() {
            var currentText = jQuery(this).text();
            jQuery(this).text(currentText.replace(' - Suggested for your Image', ''));
        });
        // Get the original text of the selected option
        var originalText = jQuery('#_ar_asset_ratio_select option[value="' + ratio + '"]').text();
        
        // Update the text content of the selected option
        jQuery('#_ar_asset_ratio_select option[value="' + ratio + '"]').text(originalText + ' - Suggested for your Image');
        
        
        updatear_asset_size_options(ratio); 
        jQuery('#ar_asset_builder_texture_done').html('&#10003;');
    }  

    //var ar_update_size_function = ar_update_size_fn();
    jQuery("select#_ar_asset_ratio_select, select#_ar_asset_orientation").on('change', function() {
        var selectedRatio = jQuery('#_ar_asset_ratio_select option:selected').val();
        jQuery('#_ar_asset_ratio').val(selectedRatio);
        updatear_asset_size_options(selectedRatio);
    });  
    
    //Update the scale of the model
    jQuery("select#ar_asset_size").on('change',function(){
        this.suffix ='';
        var selectedSize = jQuery('#ar_asset_size option:selected').val();
        jQuery('#_ar_x' + this.suffix).val(selectedSize);
        jQuery('#_ar_y' + this.suffix).val(selectedSize);
        jQuery('#ar_asset_builder_size_done').html('&#10003;');
        const modelViewer = document.querySelector('#model_'+document.getElementById("_ar_asset_id").value);
        var x = document.getElementById('_ar_x').value;
        var y = document.getElementById('_ar_y').value;
        var z = document.getElementById('_ar_z').value;

        const updateScale = () => {
          modelViewer.scale = x +' '+ y +' '+ z;
        };
        updateScale();
        
    });


    function calculateImageRatio() {
      var imageUrl = jQuery('#_asset_texture_file_0').val();
      // Create an image element dynamically
      var img = new Image();

      // Set the source URL for the image
      img.src = imageUrl;

      // Wait for the image to load
      img.onload = function() {
        // Determine if the image is landscape or portrait
        var orientation;
        if (img.width > img.height) {
          orientation = 'landscape';
        } else if (img.width < img.height) {
          orientation = 'portrait';
        } else {
          orientation = 'square';
        }

        // Set the longer dimension as width
        var width = (orientation === 'landscape') ? img.width : img.height;
        var height = (orientation === 'landscape') ? img.height : img.width;

        // Update the select field with the orientation
        //jQuery('#_ar_asset_orientation').find('option[value="' + orientation + '"]').prop('selected', true);
        jQuery('#_ar_asset_orientation').val(orientation);
        // Calculate the width-to-height ratio
        var ratio = width / height;

        // Define the target ratios
        //var targetRatios = [2 / 3, 4 / 5, 3 / 4, 11 / 14, 1.4142]; // A4:A3 paper ratio is approximately 1.4142
        var targetRatios = [1.0, 1.5, 1.25, 1.33, 1.27, 1.4142]; // A4:A3 paper ratio is approximately 1.4142

        // Find the closest ratio
        var closestRatio = findClosestRatio(ratio, targetRatios);

        // Output the result
        jQuery('#_ar_asset_ratio').val(closestRatio);
        //alert('Closest Ratio: ' + closestRatio);
         // Execute the ar_update_size_fn function
        //ar_update_size_function(closestRatio);
        updatear_asset_size_options(closestRatio);
      };
    }

    // Function to find the closest ratio
    function findClosestRatio(actualRatio, targetRatios) {
      var closestRatio = targetRatios[0];
      var minDifference = Math.abs(actualRatio - targetRatios[0]);

      for (var i = 1; i < targetRatios.length; i++) {
        var difference = Math.abs(actualRatio - targetRatios[i]);
        if (difference < minDifference) {
          minDifference = difference;
          closestRatio = targetRatios[i];
        }
      }

      return closestRatio;
    }
    function asset_display_thumb() {
        
        var imageUrl = jQuery('#_asset_texture_file_0').val();
        jQuery('#asset_thumb_img').attr('src', imageUrl);
    }
    
    // Trigger the function when the value of _asset_texture_file_0 changes
    jQuery('#_asset_texture_file_0').on('input', calculateImageRatio);
    jQuery('#_asset_texture_file_0').on('input', asset_display_thumb);  
};


ARModelFields.prototype.modelFields = function(){
    const modelViewer = document.querySelector('#model_'+this.modelid);
    const modelid = this.modelid;

    document.getElementById('_ar_disable_zoom').addEventListener('change', function() {
        if (document.getElementById("_ar_disable_zoom").checked == true){
            modelViewer.setAttribute("disable-zoom",true);
        }else{
            modelViewer.removeAttribute("disable-zoom");
        }
    });

    document.getElementById('_ar_resizing').addEventListener('change', function() {
        if (document.getElementById("_ar_resizing").checked == true){
            modelViewer.setAttribute("ar-scale","fixed");
        }else{
            modelViewer.setAttribute("ar-scale","auto");
        }
    });

    document.getElementById('_ar_rotate').addEventListener('change', function() {
        if (document.getElementById("_ar_rotate").checked == true){
            modelViewer.removeAttribute("auto-rotate");
        }else{
            modelViewer.setAttribute("auto-rotate",true);
        }
    });
    if(this.options.public != 'y'){
        document.getElementById('_glb_file').addEventListener('change', function() {
            
            modelViewer.setAttribute("src", this.value);
            var element2 = document.getElementById("ar_admin_model");
            if (element2) {
                element2.style.display = "block";
            }
        });

        jQuery(document).on('change','#_skybox_file', function(e) {
            //console.log('skybox changed' + jQuery(this).val());
            
            modelViewer.setAttribute("skybox-image", jQuery(this).val());
        });

       jQuery(document).on('change','#_ar_environment', function(e) {
            
            modelViewer.setAttribute("environment-image", jQuery(this).val());
        });
        jQuery(document).on('change','#_ar_emissive', function(e) {
           
            var value = this.value;
            
            if (value === 'False') {
                modelViewer.removeAttribute("emissive");
            } else {
                modelViewer.setAttribute("emissive", "True");
            }
        });
    
        document.getElementById('_ar_placement').addEventListener('change', function() {
            
            if (this.value == 'floor'){
                modelViewer.setAttribute("ar-placement", '');
            }else{
                modelViewer.setAttribute("ar-placement", this.value);
            }
        });
    }

    document.getElementById('_ar_zoom_in').addEventListener('change', function() {
        
        if (this.value == 'default'){
            modelViewer.setAttribute("min-camera-orbit", 'auto auto 20%');
        }else{
            const min_orbit_arr = modelViewer.getAttribute("min-camera-orbit").split(" ");
            modelViewer.setAttribute("min-camera-orbit", min_orbit_arr[0]+' '+min_orbit_arr[1]+' '+(100 - this.value) +'%');
            
        }
    });
    document.getElementById('_ar_zoom_out').addEventListener('change', function() {            
        if (this.value == 'default'){
            modelViewer.setAttribute("max-camera-orbit", 'Infinity auto 300%');
        }else{
            const max_orbit_arr = modelViewer.getAttribute("max-camera-orbit").split(" ");
            modelViewer.setAttribute("max-camera-orbit", max_orbit_arr[0]+' '+max_orbit_arr[1]+' '+(((this.value/100)*400)+100) +'%');
        }
    });
    document.getElementById('_ar_field_of_view').addEventListener('change', function() {            
        if (this.value == 'default'){
            modelViewer.setAttribute("field-of-view", '');
        }else{
            modelViewer.setAttribute("field-of-view", this.value +'deg');
        }
    });
    document.getElementById('_ar_environment_image').addEventListener('change', function() {            
        if (document.getElementById("_ar_environment_image").checked == true){
            modelViewer.setAttribute("environment-image", 'legacy');
        }else{
            modelViewer.setAttribute("environment-image", '');
        }
    });
    document.getElementById('_ar_emissive').addEventListener('change', function() {
        
        var isChecked = document.getElementById("_ar_emissive").checked;
    
        if (isChecked) {
            modelViewer.setAttribute("emissive", "True");
        } else {
            modelViewer.removeAttribute("emissive");
        }
    });
    document.getElementById('_ar_exposure').addEventListener('change', function() {
        
        modelViewer.setAttribute("exposure", this.value);
    });
    document.getElementById('_ar_shadow_intensity').addEventListener('change', function() {
        
        modelViewer.setAttribute("shadow-intensity", this.value);
    });
    document.getElementById('_ar_shadow_softness').addEventListener('change', function() {
       
        modelViewer.setAttribute("shadow-softness", this.value);
    });
    document.getElementById('_ar_light_color').addEventListener('change', function() {
        
        modelViewer.setAttribute("light-color", this.value);
    });

                
    document.getElementById('_ar_view_hide').addEventListener('change', function() {
        var element = document.getElementById("ar-button_"+modelid);
        if (document.getElementById("_ar_view_hide").checked == true){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });
    
    document.getElementById('_ar_qr_hide').addEventListener('change', function() {
        var element = document.getElementById("ar-qrcode_"+modelid);
        if (document.getElementById("_ar_qr_hide").checked == true){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });
    
    document.getElementById('_ar_hide_dimensions').addEventListener('change', function() {
        var element = document.getElementById("controls");
        var element_checkbox = document.getElementById("show-dimensions_"+modelid);
        if (document.getElementById("_ar_hide_dimensions").checked == true){
            element.style.display = "none";
            element_checkbox.checked = false;
            
            modelViewer.querySelectorAll('button').forEach((hotspot) => {
              if ((hotspot.classList.contains('dimension'))||(hotspot.classList.contains('dot'))){
                    hotspot.classList.add('nodisplay');
              }
            });
        }else{
            element.style.display = "block";
        }
    });
    
    document.getElementById('_ar_hide_reset').addEventListener('change', function() {
        var element = document.getElementById("ar-reset_"+modelid);
        if (document.getElementById("_ar_hide_reset").checked == true){
            element.style.display = "none";
        }else{
            element.style.display = "block";
        }
    });
    
    [ _ar_x, _ar_y, _ar_z ].forEach(function(element) {
        element.addEventListener('change', function() {
            var x = document.getElementById('_ar_x').value;
            var y = document.getElementById('_ar_y').value;
            var z = document.getElementById('_ar_z').value;

            const updateScale = () => {
              modelViewer.scale = x +' '+ y +' '+ z;
            };
            updateScale();
        });
    });
    document.getElementById('_ar_animation').addEventListener('change', function() {
        var element = document.getElementById("ar-button-animation");
        if (document.getElementById("_ar_animation").checked == true){
            element.style.display = "block";
        }else{
            element.style.display = "none";
        }
    });
};


/*
jQuery(function($) { 
    //Asset Builder
    // Initialize a flag to track whether the action has been triggered
    // Find the "Update" button in the Gutenberg editor
    const updateButton = $('.editor-post-save-draft');

    // Find your custom button by its class or ID
    const customButton = $('#ar_asset_builder_submit');

    // Add a click event handler to your custom button
    customButton.on('click', function() {
    
        var asset_file = $('#_ar_asset_file').val();
        var asset_orientation = $('#_ar_asset_orientation').val();
        var asset_ratio = $('#ar_asset_ratio').val();
        var m = asset_file.lastIndexOf('.');
        
        //console.log(\'Asset File:\', asset_file);
        //console.log(\'Last Index of Dot:\', m);
        
        if (m !== -1) {
            var asset_file_result = asset_file.substring(0, m);
            console.log('Result:', asset_file_result);
        } else {
            console.log("No dot found in asset_file");
        }
        //console.log(\'Asset Ratio:\', asset_ratio);
        //console.log(\'Asset Orientation:\', asset_orientation);
        var asset_file_result = asset_file.substring(0, m) + '_' + asset_ratio + '_' + asset_orientation + '.zip' ;
        $('#_ar_asset_file').val(asset_file_result);
        $('#_ar_placement').val('wall');
        //alert (asset_file_result);
        
        wp.data.dispatch('core/editor').savePost();
        // Reload the page after a short delay to ensure the save action completes
        setTimeout(function() {
            location.reload();
        }, 1000); 
    });
    //asset_builder_button
    $( "#asset_builder_tab, #asset_builder_button" ).click(function() {
            $("#asset_builder_iframe").html('<iframe src="https://augmentedrealityplugins.com/asset_builder/gallery.php?referrer='+ options.site_url +'" style="width:100%;min-height:180px" id="asset_builder_iframe"></iframe>');
    });
});   */ 

    /*$( "#ar_asset_iframe_acc" ).click(function() {
        if ($("#asset_builder_iframe").html() === \'\') {
            $("#asset_builder_iframe").html(\'<iframe src="https://augmentedrealityplugins.com/asset_builder/gallery.php?referrer='.urlencode(get_site_url()).'" style="width:100%;height:160px" id="asset_builder_iframe"></iframe>\');
        }
    });*/
//});
//List for Events from the Asset Builder iFrame
var eventMethod = window.addEventListener
        ? "addEventListener"
        : "attachEvent";
var eventer = window[eventMethod];
var messageEvent = eventMethod === "attachEvent"
    ? "onmessage"
    : "message";

eventer(messageEvent, function (e) {
    if (e.origin !== 'https://augmentedrealityplugins.com') return;
    if (e.data.substring(0, 5)==='https'){
    //alert (e.data);
        document.getElementById('_ar_asset_file').value = e.data;
        if (document.getElementById('_asset_texture_file_0').value) {
            document.getElementById('ar_asset_builder_model_done').innerHTML = '&#10003;';
            document.getElementById('ar_asset_builder_submit_container').style.display = 'block';
        }
        //ar_update_size_function();
    }else{
        //Show texture input fields and update their labels
        var details = e.data.split(',');
        document.getElementById('_asset_texture_flip').value = '';
        var i;
        for (i = 0; i < 1; i++) { //Previously 10 - Cube will require 6
          var texture = 'texture_' + i;
          var label = 'texture_label_' + i;
          var btn = 'upload_asset_texture_button_' + i;
          var field = '_asset_texture_file_' + i;
          var field_id = '_asset_texture_id_' + i;
          var element = document.getElementById('texture_container_' + i);
          element.classList.add("nodisplay");
          if(details[i] === undefined){
              document.getElementById(field).value = '';
              document.getElementById(field_id).value = '';
          }else if (details[i] ==='flip'){
              //alert(details[i]);
              document.getElementById('_asset_texture_flip').value = 'flip';
          }else{
              //element.classList.remove("nodisplay");
              element.classList.remove("nodisplay");
              //document.getElementById(\'texture_\' + i).classList.remove("nodisplay");
              
              var label_contents = details[i].charAt(0).toUpperCase() + details[i].slice(1);
              label_contents=label_contents.substring(0,(label_contents.length -4));
              document.getElementById(field_id).value = details[i];
              document.getElementById(btn).value = label_contents.replace('_',' ');
          }
        
        }
    }
    
});
