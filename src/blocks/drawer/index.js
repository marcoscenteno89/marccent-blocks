/*
 * Drawer: a button and the dialog it opens (shared/dialog.js has the editor; render.php writes both).
 */
import { register } from '../../shared/register';
import { InnerBlocks } from '@wordpress/block-editor';
import { DialogEdit } from '../../shared/dialog';
import metadata from './block.json';

const looks = { key: 'side', label: 'Slides in from', options: [ { label: 'The end (right)', value: 'end' }, { label: 'The start (left)', value: 'start' } ] };
register( metadata, { edit: ( props ) => <DialogEdit { ...props } looks={ looks } note={ `Slides in from the ${ props.attributes.side } when the button is clicked` } />, save: () => <InnerBlocks.Content /> } );
