/*
 * An Orbit saved with its items as a list (before T-034): its items become its child blocks (src/shared/upgrade.js).
 */
import metadata from './block.json';
import { listToChildren } from '../../shared/upgrade';

export default [ listToChildren( metadata, 'items' ) ];
