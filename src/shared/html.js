/*
 * Server-rendered blocks inside an island (a tab's, a card's buttons): HTML as React, in a box that takes no room of
 * its own (display: contents), so the blocks lay out as if they were the component's children.
 */
export const Html = ( { html } ) => <div style={ { display: 'contents' } } dangerouslySetInnerHTML={ { __html: html } } />;
// The blocks only: the props' stand-in text in the slot (data-island-text, inc/islands.php) is the component's to say.
export const blocksOf = ( html = [] ) => html.filter( ( h ) => ! h.startsWith( '<div data-island-text' ) );
export const HtmlList = ( { html = [] } ) => blocksOf( html ).map( ( h, i ) => <Html key={ i } html={ h } /> );
