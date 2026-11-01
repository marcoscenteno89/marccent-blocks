/*
 * Modal: a button and the dialog it opens (shared/dialog.js has the editor; render.php writes both).
 */
import { register } from '../../shared/register';
import { InnerBlocks } from '@wordpress/block-editor';
import { DialogEdit } from '../../shared/dialog';
import metadata from './block.json';

const looks = { key: 'variant', label: 'Look', options: [ { label: 'Default', value: '' }, { label: 'Ticket', value: 'ticket' } ] };
register( metadata, { edit: ( props ) => <DialogEdit { ...props } looks={ looks } note={ 'Opens over the page when the button is clicked' } />, save: () => <InnerBlocks.Content /> } );
