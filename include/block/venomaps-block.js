( function( blocks, element, blockEditor, components ) {
    const registerBlockType = blocks.registerBlockType,
    Placeholder = components.Placeholder,
    SelectControl = components.SelectControl,
    PanelBody = wp.components.PanelBody,
    TextControl = components.TextControl;

    var el = element.createElement;
    var templates_active = [];
    var templates_active = [ { label: '--', value : '' } ];
    var templates = JSON.parse( venomapsBlockVars.templates );

    Object.keys(templates).forEach(function(index) {
      templates_active.push({ label: templates[index], value : index });  
    });

    const unit_list = [
        { label: 'px', value: 'px' },
        { label: 'vh', value: 'vh' }
    ];

    registerBlockType( 'venomaps/venomap', {
        title: 'VenoMaps',
        // description: '',
        icon: 'location-alt',
        category: 'widgets',
        attributes: {
          content: {
            type: 'array',
            source: 'children',
            selector: 'div',
          },
          map_id: { type: 'string', default: '' },
          height: { type: 'string', default: '500' },
          height_um: { type: 'string', default: 'px' }
        },

        edit: function( props ) {
            var map_id_init = props.attributes.map_id || '';
            var height_init = props.attributes.height || '500';
            var height_um_init = props.attributes.height_um || 'px';
            return [
                el(
                    Placeholder, 
                    {
                        key: 'venomap-placeholder',
                        icon: 'location-alt',
                        label: "VenoMap",
                    },
                    el(
                        SelectControl,
                        {
                            label: venomapsBlockVars._select_map,
                            // help : ' ',
                            options: templates_active,
                            value: map_id_init,
                            onChange: function(value) {
                                props.setAttributes({map_id: value});
                            },
                            className: 'venomaps_block_map_id'
                        }
                    ),
                    el(
                        TextControl,
                        {
                            label: venomapsBlockVars._map_height,
                            type: 'number',
                            value: height_init,
                            onChange: function(value) {
                                var state = value ? value : 500;
                                props.setAttributes({height: state});
                            },
                            className: 'venomaps_block_map_height'
                        },
                    ),
                    el(
                        SelectControl,
                        {
                            label: venomapsBlockVars._units,
                            options: unit_list,
                            value: height_um_init,
                            onChange: function(value) {
                                var state = value ? value : 'px';
                                props.setAttributes({height_um: state});
                            },
                            className: 'venomaps_block_map_units'
                        }
                    )
                )
            ]; // return 
        },
        save: function (props) {
            return el(
                'div', {}, '[venomap id="' + props.attributes.map_id + '" height="' + props.attributes.height + props.attributes.height_um + '"]'
            )
        }
    } );
}(
  window.wp.blocks,
  window.wp.element,
  window.wp.blockEditor,
  window.wp.components
) );