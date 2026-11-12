/*
 * A Step bar saved with its steps as a list (before T-034): its steps become its child blocks (src/shared/upgrade.js).
 */
import metadata from './block.json';
import { listToChildren } from '../../shared/upgrade';

export default [ listToChildren( metadata, 'steps' ) ];
