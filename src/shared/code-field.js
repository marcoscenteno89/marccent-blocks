/*
 * CodeField: a code setting in the block sidebar, edited in marccent-theme's Code (CodeMirror, highlighted) instead of
 * a plain textarea: the Code block's code (island.js, type: 'code') and every block's Additional CSS (custom-css.js).
 * Labelled and boxed like WordPress's own controls around it.
 */
import { BaseControl, useBaseControlProps } from '@wordpress/components';
import { Code } from 'marccent-theme/components/ui/code';

export const CodeField = ( { label, help, lang = 'css', value, onChange, minLines = 6 } ) => {
	const { baseControlProps } = useBaseControlProps( { label, help, __nextHasNoMarginBottom: true } );
	return (
		<BaseControl { ...baseControlProps }>
			<div style={ { border: '1px solid #949494', borderRadius: 2, background: '#fff', color: '#1e1e1e' } }>
				<Code lang={ lang } label={ label } minLines={ minLines } onChange={ onChange }>{ value }</Code>
			</div>
		</BaseControl>
	);
};
