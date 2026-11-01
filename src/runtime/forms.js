/*
 * The Form block without leaving the page: a submit is sent to the REST route (inc/forms.php, the same checks as the
 * no-JavaScript post), the button shows it's busy, then the form says how it went: the success message (and empties
 * itself), or each error beside its field (aria-invalid, aria-describedby) and the first one focused. Turnstile, when
 * the form has it, gets a new token after a failure.
 */
const STATUS = { ok: 'bg-success', error: 'bg-danger' };

const clear = ( form ) => {
	form.querySelectorAll( '[data-field-error]' ).forEach( ( p ) => p.remove() );
	form.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => {
		el.removeAttribute( 'aria-invalid' );
		const ids = ( el.getAttribute( 'aria-describedby' ) || '' ).split( ' ' ).filter( ( id ) => id && ! id.endsWith( '-error' ) );
		ids.length ? el.setAttribute( 'aria-describedby', ids.join( ' ' ) ) : el.removeAttribute( 'aria-describedby' );
	} );
};

const say = ( form, tone, text ) => {
	const status = form.querySelector( '[data-form-status]' );
	status.replaceChildren();
	if ( text ) {
		const box = document.createElement( 'div' );
		box.className = `pad border round ${ STATUS[ tone ] }`;
		box.dataset.component = 'alert';
		box.textContent = text;
		status.append( box );
	}
};

// Each error beside its field: under the field's row, pointed to by its control.
const showErrors = ( form, errors ) => {
	let first = null;
	Object.entries( errors ).forEach( ( [ name, message ] ) => {
		const controls = [ ...form.elements ].filter( ( el ) => el.name === name );
		const row = controls[ 0 ]?.closest( '[data-component="field"]' );
		if ( ! controls.length || ! row ) {
			return;
		}
		const id = `${ form.id }-${ name }-error`;
		const p = Object.assign( document.createElement( 'p' ), { id, className: 'fs-sm text-secondary', textContent: message } );
		p.dataset.fieldError = '';
		row.after( p );
		// A rating's or tags' controls are hidden; the visible one (its fieldset, its text box) says it's invalid.
		const target = controls[ 0 ].type === 'hidden' ? row.querySelector( 'input:not([type=hidden])' ) : controls[ 0 ].type === 'radio' ? controls[ 0 ].closest( 'fieldset' ) : controls[ 0 ];
		[ target, ...controls ].filter( Boolean ).forEach( ( el ) => {
			el.setAttribute( 'aria-invalid', 'true' );
			el.setAttribute( 'aria-describedby', [ el.getAttribute( 'aria-describedby' ), id ].filter( Boolean ).join( ' ' ) );
		} );
		first ??= target;
	} );
	first?.focus?.();
};

const send = async ( form, button ) => {
	button?.setAttribute( 'aria-busy', 'true' );
	button && ( button.disabled = true );
	clear( form );
	try {
		const response = await fetch( form.dataset.rest, {
			method: 'POST',
			body: new FormData( form ),
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': form.dataset.restNonce },
		} );
		const json = await response.json().catch( () => ( {} ) );
		if ( response.ok && json.ok ) {
			say( form, 'ok', json.message );
			form.reset();
			return;
		}
		say( form, 'error', json.message || 'Something went wrong. Try again.' );
		showErrors( form, json.errors || {} );
		window.turnstile?.reset( form.querySelector( '.cf-turnstile' ) );
	} catch {
		say( form, 'error', 'Couldn’t reach the site. Check the connection and try again.' );
	} finally {
		button?.removeAttribute( 'aria-busy' );
		button && ( button.disabled = false );
	}
};

export const enhanceForms = () => document.querySelectorAll( 'form[data-marccent-form]' ).forEach( ( form ) => {
	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		send( form, e.submitter || form.querySelector( '[type=submit]' ) );
	} );
} );
