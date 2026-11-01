/*
 * Stopwatch: Start and Stop around the theme's Counter (shared/stopwatch.js).
 */
import { islandBlock } from '../../shared/island';
import { StopwatchOf } from '../../shared/stopwatch';
import metadata from './block.json';

islandBlock( metadata, { preview: () => <StopwatchOf /> } );
