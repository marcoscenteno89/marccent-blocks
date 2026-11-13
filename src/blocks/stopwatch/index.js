/*
 * Stopwatch: the theme's Counter with its own controls (Start / Stop and the last time).
 */
import { islandBlock } from '../../shared/island';
import { Counter } from 'marccent-theme/components/ui/counter';
import metadata from './block.json';

islandBlock( metadata, { component: 'Counter', preview: () => <Counter controls /> } );
