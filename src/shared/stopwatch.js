/*
 * A stopwatch: the theme's Counter (it runs while active) between a Start and a Stop button, and the last time
 * stopped.
 */
import { useState } from 'react';
import { Counter } from 'marccent/components/ui/counter';
import { Button } from 'marccent/components/ui/button';

export const StopwatchOf = () => {
	const [ active, setActive ] = useState( false );
	const [ last, setLast ] = useState( null );
	return (
		<div data-component="stopwatch" className="grid auto-cols items-center gap-sm">
			<Button icon={ active ? 'stop' : 'play' } className={ active ? 'danger' : 'primary' } onClick={ () => setActive( ! active ) }>{ active ? 'Stop' : 'Start' }</Button>
			<Counter active={ active } getTime={ setLast } />
			{ last && <small className="opacity-75">Last: { `${ last.hour }:${ last.minute }:${ last.second }.${ last.tos }` }</small> }
		</div>
	);
};
