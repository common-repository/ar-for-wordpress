class ARModelViewer {    
    constructor(containerId, options = {}) {
        this.modelid = containerId;
        this.options = Object.assign(options);  
        this.suffix = '';        
        this.modelViewer = document.getElementById('model_' + this.modelid + this.suffix);
        this.initARModelViewer();
    }

    initARModelViewer() {     
        this.setScale();        
        this.setDimensions();
        this.setHotspots();

        if (this.options.ar_animation) {
            this.setAnimation();
        }

        if (this.options.ar_model_list > 1) {
            this.setSlider();
        }

        if (this.options.ar_variants) {
            this.setVariants();
        }

        if (this.options.ar_pop == 'pop') {
            this.popUp();
        }

        // Initialize the scale change handler
        this.handleScaleChange();
    }

    handleScaleChange() {
        // Check if the select#scale element exists
        const scaleSelect = document.querySelector("select#scale");

        if (scaleSelect) {
            scaleSelect.addEventListener('change', () => {
                // Get the selected value
                const selectedSize = scaleSelect.value;

                // Update the scale of the model viewer
                const updateScale = () => {
                    this.modelViewer.scale = `${selectedSize} ${selectedSize} ${selectedSize}`;
                };

                updateScale();
            });
        }
    }

    setScale() {
        if (isNumeric(this.options.ar_x) && isNumeric(this.options.ar_y) && isNumeric(this.options.ar_z) && this.options.ar_pop !== 'pop') {
            const modelViewerTransform = document.getElementById('model_' + this.modelid + this.suffix);
            const updateScale = () => {
                modelViewerTransform.scale = this.options.ar_x + ' ' + this.options.ar_y + ' ' + this.options.ar_z;
            };
            updateScale();
        }
    }

    setVariants(){
        const modelViewerVariants = document.getElementById('model_' + this.modelid + this.suffix);
        const select = document.getElementById('variant_'+this.modelid + this.suffix);
        
        modelViewerVariants.addEventListener('load', () => {
        const names = modelViewerVariants.availableVariants;
        for (const name of names) {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            select.appendChild(option);
          }
        });
        
        select.addEventListener('input', (event) => {
          modelViewerVariants.variantName = event.target.value;
        });
    }

    setScale() {
      if(isNumeric(this.options.ar_x) && isNumeric(this.options.ar_x) && isNumeric(this.options.ar_x) && this.options.ar_pop!='pop'){
        const modelViewerTransform = document.getElementById('model_' + this.modelid  + this.suffix);       
        const updateScale = () => {
          modelViewerTransform.scale = this.options.ar_x + ' ' + this.options.ar_y + ' ' + this.options.ar_z;
        };
        updateScale();
      }
    }


    setSlider(){
        window.switchSrc = (modelid, buttonElement, name, usdz) => {
            var modelViewerList = document.querySelector("#" + modelid);
            if (modelViewerList) {
                modelViewerList.src = name;
                modelViewerList.poster = name;
                modelViewerList.iosSrc = usdz;
        
                const slides = document.querySelectorAll(".ar_slide");
                slides.forEach((element) => {
                    element.classList.remove("selected");
                });
        
                if (buttonElement && buttonElement.classList) {
                    buttonElement.classList.add("selected"); // Use the passed element to add the class
                } else {
                    console.error("buttonElement is not defined or does not have a classList.");
                }
            } else {
                console.error("Model viewer element not found.");
            }
        };

        document.querySelector(".ar_slider").addEventListener('beforexrselect', (ev) => {
            // Keep slider interactions from affecting the XR scene.
            ev.preventDefault();
        });
    }


    setDimensions(){       
        //console.log('#src_'+this.modelid+this.suffix);
        this.modelViewer.querySelector('#src_'+this.modelid+this.suffix).addEventListener('input', (event) => {
          this.modelViewer.src = event.target.value;
        });
        const checkbox = this.modelViewer.querySelector('#show-dimensions_' + this.modelid  + this.suffix);
        //console.log('#show-dimensions_' + this.modelid  + this.suffix);
        checkbox.addEventListener('change', () => {

          this.modelViewer.querySelectorAll('button').forEach((hotspot) => {
              if ((hotspot.classList.contains('dimension'))||(hotspot.classList.contains('dot'))){
                if (checkbox.checked) {
                  hotspot.classList.remove('nodisplay');
                } else {
                  hotspot.classList.add('nodisplay');
                }
              }
            
              if (this.options.ar_hide_fullscreen==''){
              
                 if (document.getElementById('ar_pop_Btn_'+this.options.id).classList.contains('nodisplay')){
                      document.getElementById('ar_pop_Btn_'+this.options.id).classList.remove('nodisplay');
                      document.getElementById('ar_close_'+this.modelid  + this.suffix).classList.remove('nodisplay');
                 }                
                
                 document.getElementById('ar-button_'+this.modelid + this.suffix).classList.remove('nodisplay');
                 document.getElementById('ar-qrcode_'+this.modelid + this.suffix).classList.remove('nodisplay');
              }
          });
      });

      this.modelViewer.querySelector('button.dimension').addEventListener('click', (event) => {
          event.preventDefault();
        });
    }

    setHotspots(){
        this.modelViewer.addEventListener('load', () => {
            const center = this.modelViewer.getCameraTarget();
            const size = this.modelViewer.getDimensions();
            const x2 = size.x / 2;
            const y2 = size.y / 2;
            const z2 = size.z / 2;
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot+X-Y+Z',
              position: `${center.x + x2} ${center.y - y2} ${center.z + z2}`
            });
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dim+X-Y',
              position: `${center.x + x2} ${center.y - y2} ${center.z}`
            });

            var hotspotx; 
            var hotspoty; 
            var hotspotz;

            if ((this.options.ar_dimensions_units == 'inches') || (this.options.ar_dimensions_inches == true)){
                hotspotz = `${(size.z * 39.370).toFixed(2)} in`;
            } else if (this.options.ar_dimensions_units == 'cm'){
                hotspotz = `${(size.z * 100).toFixed(0)} cm`;
            } else if (this.options.ar_dimensions_units == 'mm'){
                hotspotz = `${(size.z * 1000).toFixed(0)} mm`;
            } else {
                hotspotz = `${(size.z).toFixed(2)} m`;
            }
            this.modelViewer.querySelector('button[slot="hotspot-dim+X-Y"]').textContent = hotspotz;
                     
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot+X-Y-Z',
              position: `${center.x + x2} ${center.y - y2} ${center.z - z2}`
            });
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dim+X-Z',
              position: `${center.x + x2} ${center.y} ${center.z - z2}`
            });

            if ((this.options.ar_dimensions_units == 'inches') || (this.options.ar_dimensions_inches == true)){
                hotspoty = `${(size.y * 39.370).toFixed(2)} in`;
            } else if (this.options.ar_dimensions_units == 'cm'){
                hotspoty = `${(size.y * 100).toFixed(0)} cm`;
            } else if (this.options.ar_dimensions_units == 'mm'){
                hotspoty = `${(size.y * 1000).toFixed(0)} mm`;
            } else {
                hotspoty = `${(size.y).toFixed(2)} m`;
            }

            this.modelViewer.querySelector('button[slot="hotspot-dim+X-Z"]').textContent = hotspoty;
                        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot+X+Y-Z',
              position: `${center.x + x2} ${center.y + y2} ${center.z - z2}`
            });
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dim+Y-Z',
              position: `${center.x} ${center.y + y2} ${center.z - z2}`
            });

            if ((this.options.ar_dimensions_units == 'inches') || (this.options.ar_dimensions_inches == true)){
                hotspotx = `${(size.x * 39.370).toFixed(2)} in`;
            } else if (this.options.ar_dimensions_units == 'cm'){
                hotspotx = `${(size.x * 100).toFixed(0)} cm`;
            } else if (this.options.ar_dimensions_units == 'mm'){
                hotspotx = `${(size.x * 1000).toFixed(0)} mm`;
            } else {
                hotspotx = `${(size.x).toFixed(2)} m`;
            }

            this.modelViewer.querySelector('button[slot="hotspot-dim+Y-Z"]').textContent = hotspotx;
                        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot-X+Y-Z',
              position: `${center.x - x2} ${center.y + y2} ${center.z - z2}`
            });
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dim-X-Z',
              position: `${center.x - x2} ${center.y} ${center.z - z2}`
            });

            this.modelViewer.querySelector('button[slot="hotspot-dim-X-Z"]').textContent  = hotspoty;
                       
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot-X-Y-Z',
              position: `${center.x - x2} ${center.y - y2} ${center.z - z2}`
            });
        
            this.modelViewer.updateHotspot({
              name: 'hotspot-dim-X-Y',
              position: `${center.x - x2} ${center.y - y2} ${center.z}`
            });

            this.modelViewer.querySelector('button[slot="hotspot-dim-X-Y"]').textContent = hotspotz;
            
            this.modelViewer.updateHotspot({
              name: 'hotspot-dot-X-Y+Z',
              position: `${center.x - x2} ${center.y - y2} ${center.z + z2}`
            });
            
          });
    }

    popUp(){

        //ar_pop_Btn_100
        //console.log("ar_pop_Btn_" + this.options.id);

        var ar_pop_ = document.getElementById("ar_popup_" + this.options.id);
        var ar_close_ = document.getElementById("ar_close_" + this.options.id + "_pop");
        if(document.getElementById("ar_pop_Btn_" + this.options.id) !== null){
            document.getElementById("ar_pop_Btn_" + this.options.id).onclick = function() {
              ar_pop_.style.display = "block";
            }
        }
        ar_close_.onclick = function() {
          ar_pop_.style.display = "none";
        }
        window.onclick = function(event) {
          if (event.target == ar_pop_) {
            ar_pop_.style.display = "none";
          }
        }
    }
}

function getData(){
  
}

function isNumeric(value) {
    return typeof value === 'number' && isFinite(value);
}