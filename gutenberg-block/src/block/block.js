/**
 * BLOCK: arwp-guten-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType, InspectorControls } = wp.blocks; // Import registerBlockType() from wp.blocks
const { registerPlugin } = wp.plugins;
const { PluginDocumentSettingPanel } = wp.editPost;
const { withSelect, withDispatch, dispatch, select, useSelect } = wp.data;

const { SelectControl,
	    Toolbar,
	    Button,
	    Tooltip,
	    PanelBody,
	    PanelRow,
	    FormToggle,
	    ToggleControl,
	    ToolbarGroup,
	    Disabled,
	    RadioControl,
	    RangeControl,
	    FontSizePicker } = wp.components;

const { Component } = wp.element;
import apiFetch from '@wordpress/api-fetch';
const { serverSideRender: ServerSideRender } = wp;
/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'cgb/block-arwp-guten-block', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'AR For Wordpress' ), // Block title.
	icon: 'smartphone', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'ar_display', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'AR' ),                    // Add shorter term "AR"
        __( 'Augmented Reality' ),      // Keyword for searchability
        __( 'AR For WordPress' ),       // Block name
        __( 'Augmented Reality For WordPress' ),// More descriptive keyword
	],

	attributes: {
	    id: {
	      type: 'number',
	      default: 0,
	    },	    
	  },
	
	/**
	 * The edit function describes the structure of your block in the context of the editor.
	 * This represents what the editor will render when the block is used.
	 *
	 * The "edit" property must be a valid function.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
	 *
	 * @param {Object} props Props.
	 * @returns {Mixed} JSX Component.
	 */
	edit:  ( props ) => {
		// Creates a <p class='wp-block-cgb-block-arwp-guten-block'></p>.

		const onChangeModel = ( newModel ) => {
			props.setAttributes( { id: Number(newModel) } );			
		}

	
		const { pages } = useSelect( ( select ) => {
			const { getEntityRecords } = select( 'core' );

			// Query args
			const query = {
				status: 'publish',
				per_page: -1
			}

			return {
				pages: getEntityRecords( 'postType', 'armodels', query ),
			}
		} )
		//console.log(pages);
		// populate options for <SelectControl>
		let options = []
		if( pages ) {
			options.push( { value: 0, label: 'Select a model' } )
			pages.forEach( ( page ) => {
				options.push( { value : page.id, label : page.title.rendered } )
			})
		} else {
			options.push( { value: 0, label: 'Loading...' } )
		}
		
		console.log(props.attributes)

    	return [

	        <div>	            
	            <SelectControl
                    label="AR Model"
                    value={ props.attributes.id }
                    options= { options }                        
                    onChange = { onChangeModel }
                />

                <Disabled>
					<ServerSideRender block="cgb/block-arwp-guten-block" attributes={ props.attributes } />
				</Disabled>	            
	        </div>
	    ]

	},
	save: ( props ) => {

	    return null;
  
	},
} );
