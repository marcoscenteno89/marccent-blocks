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

/*
 * Fields shown only when another has a value (data-show-if: "service=Patios,Driveways", "contact!=Phone", "phone"):
 * hidden, their controls are disabled, so they aren't required and aren't sent (the server checks the same).
 */
const conditions = ( form ) => {
	const boxes = [ ...form.querySelectorAll( '[data-show-if]' ) ];
	if ( ! boxes.length ) return () => {};
	const valueOf = ( name ) => {
		const controls = [ ...form.elements ].filter( ( el ) => el.name === name && ! el.disabled );
		const on = controls.find( ( el ) => ( el.type === 'radio' || el.type === 'checkbox' ? el.checked : true ) );
		return on ? String( on.value ).trim() : '';
	};
	const update = () => boxes.forEach( ( box ) => {
		const m = box.dataset.showIf.match( /^([a-z0-9_-]+)\s*(?:(!=|=)\s*(.*))?$/i );
		let shown = true;
		if ( m ) {
			const have = valueOf( m[ 1 ] );
			const isIn = ( m[ 3 ] || '' ).split( ',' ).map( ( x ) => x.trim() ).includes( have );
			shown = ! m[ 2 ] ? have !== '' : m[ 2 ] === '=' ? isIn : ! isIn;
		}
		box.hidden = ! shown;
		box.style.display = shown ? 'contents' : 'none';
		box.querySelectorAll( 'input, select, textarea' ).forEach( ( el ) => ( el.disabled = ! shown ) );
	} );
	form.addEventListener( 'input', update );
	form.addEventListener( 'change', update );
	update();
	return update;
};

/*
 * Steps: a "step" field (data-form-step) starts the next step. One step shows at a time, with Back and Next; Next
 * checks the step's own fields first; the send button is on the last. Without JavaScript, one long form.
 */
const steps = ( form ) => {
	const breaks = [ ...form.querySelectorAll( '[data-form-step]' ) ];
	if ( ! breaks.length ) return null;
	const submit = form.querySelector( '[type=submit]' );
	const parts = [ [] ];
	[ ...form.children ].forEach( ( child ) => {
		if ( child.matches( '[data-form-step]' ) ) parts.push( [] );
		else if ( ! child.matches( 'input[type=hidden], [data-form-status]' ) && ! child.contains( submit ) && child !== submit ) parts[ parts.length - 1 ].push( child );
	} );
	const titles = [ '', ...breaks.map( ( b ) => b.dataset.formStep ) ];
	const nav = document.createElement( 'div' );
	nav.className = 'form-steps flex gap-sm items-center';
	nav.style.gridColumn = '1 / -1';
	const progress = document.createElement( 'p' );
	progress.className = 'fs-sm opacity-75';
	progress.style.gridColumn = '1 / -1';
	const button = ( text, cls ) => Object.assign( document.createElement( 'button' ), { type: 'button', className: `btn ${ cls }`, textContent: text } );
	const back = button( 'Back', '' );
	const next = button( 'Next', 'primary' );
	nav.append( back, next );
	( submit.closest( 'form > *' ) || submit ).before( nav );
	form.prepend( progress );
	form.prepend( ...form.querySelectorAll( ':scope > input[type=hidden]' ) );
	let at = 0;
	const show = ( i ) => {
		at = Math.max( 0, Math.min( parts.length - 1, i ) );
		parts.forEach( ( els, j ) => els.forEach( ( el ) => ( el.hidden = j !== at ) ) );
		back.hidden = at === 0;
		next.hidden = at === parts.length - 1;
		( submit.closest( 'form > *' ) || submit ).hidden = at !== parts.length - 1;
		progress.textContent = `Step ${ at + 1 } of ${ parts.length }${ titles[ at ] ? ` · ${ titles[ at ] }` : '' }`;
	};
	const valid = () => parts[ at ].flatMap( ( el ) => [ ...el.querySelectorAll( 'input, select, textarea' ) ] )
		.filter( ( el ) => ! el.disabled && el.type !== 'hidden' ).every( ( el ) => el.reportValidity() );
	next.addEventListener( 'click', () => valid() && show( at + 1 ) );
	back.addEventListener( 'click', () => show( at - 1 ) );
	show( 0 );
	// The step a field is in: after an error from the server, its step shows.
	return { show, stepOf: ( el ) => parts.findIndex( ( els ) => els.some( ( x ) => x.contains( el ) ) ), reset: () => show( 0 ) };
};

export const enhanceForms = () => document.querySelectorAll( 'form[data-marccent-form]' ).forEach( ( form ) => {
	const update = conditions( form );
	const stepper = steps( form );
	form.addEventListener( 'submit', ( e ) => {
		e.preventDefault();
		send( form, e.submitter || form.querySelector( '[type=submit]' ) ).then( () => {
			const bad = form.querySelector( '[aria-invalid=true]' );
			if ( stepper && bad ) stepper.show( stepper.stepOf( bad ) );
			else if ( stepper && ! form.querySelector( '[data-field-error]' ) && form.querySelector( '[data-form-status] .bg-success' ) ) stepper.reset();
			update();
		} );
	} );
} );
