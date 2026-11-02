import type { PluginSpec } from "prosemirror-state";
import type { EditorView } from "prosemirror-view";
import { ucfirst } from "@/helpers/string";
import type { ExtensionCommand } from "../Extension";
import Mark from "../Mark";

export interface AnchorAttrs {
	href: string;
	target?: string;
	title?: string;
}

/**
 * Base for marks that render as `<a href>`, e.g. links and emails
 *
 * @since 6.0.0
 */
export default abstract class Anchor<
	TOptions extends Record<string, unknown> = Record<string, unknown>
> extends Mark<TOptions> {
	/**
	 * Whether the href may enter the document and be opened
	 */
	protected abstract accepts(href: string | null): boolean;

	commands(): Record<string, ExtensionCommand> {
		const name = ucfirst(this.name);

		return {
			[this.name]: (event: MouseEvent) => {
				if (event.altKey || event.metaKey) {
					return this.remove();
				}

				this.editor.emit(this.name, this.editor);
			},
			[`insert${name}`]: (attrs: { href?: string } = {}) => {
				if (!attrs.href) {
					return;
				}

				const isActive = this.editor.activeMarks.includes(this.name);

				// reject hrefs the mark does not accept so they never
				// enter the document state; if the mark is already
				// active, remove it entirely
				if (this.accepts(attrs.href) === false) {
					if (isActive === true) {
						return this.remove();
					}

					return;
				}

				const { selection } = this.editor.state!;

				// if no text is selected and the mark is not active
				// we insert the href as text
				if (selection.empty && isActive === false) {
					this.editor.insertText(attrs.href, true);
				}

				return this.update(attrs);
			},
			[`remove${name}`]: () => {
				return this.remove();
			},
			[`toggle${name}`]: (attrs: { href?: string } = {}) => {
				if (attrs.href) {
					this.editor.command(`insert${name}`, attrs);
				} else {
					this.editor.command(`remove${name}`);
				}
			}
		};
	}

	/**
	 * Opens the href when the mark is alt-clicked
	 */
	protected abstract open(attrs: AnchorAttrs): void;

	plugins(): PluginSpec<null>[] {
		return [
			{
				props: {
					handleClick: (_view: EditorView, _pos: number, event: MouseEvent) => {
						const attrs = this.editor.getMarkAttrs<AnchorAttrs>(this.name)!;

						if (
							attrs.href &&
							event.altKey === true &&
							event.target instanceof HTMLAnchorElement &&
							this.accepts(attrs.href) === true
						) {
							event.stopPropagation();
							this.open(attrs);
						}
					}
				}
			}
		];
	}
}
