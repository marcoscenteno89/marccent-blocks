/*
 * A marccent/form-tags block in a form: the theme's TagInput, whose hidden input (the tags, comma-separated) is sent
 * with the form.
 */
import { TagInput } from 'marccent-theme/components/ui/tag-input';

export default function FormTagsIsland( { name, label, max = 0, suggestions, defaultValue = [] } ) {
	return <TagInput name={ name } label={ label } max={ max } suggestions={ suggestions || undefined } defaultValue={ defaultValue } className="input" />;
}
