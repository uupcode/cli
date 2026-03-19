import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import metadata from './block.json';
import './editor.css';

registerBlockType( metadata.name, {
    edit() {
        return <p { ...useBlockProps() }>{ metadata.title } — edit view</p>;
    },

    // Dynamic block — rendered via PHP. Return null here.
    save: () => null,
} );