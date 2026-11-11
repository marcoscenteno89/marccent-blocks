/*
 * A Timeline saved with its events as a list (before T-034): its events become its child blocks (src/shared/upgrade.js).
 */
import metadata from './block.json';
import { listToChildren } from '../../shared/upgrade';

export default [ listToChildren( metadata, 'events' ) ];
