import { describe, expect, it, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import writer from "@/helpers/writer";
import Editor from "@/components/Forms/Writer/Editor";
import WriterInput from "@/components/Forms/Input/WriterInput.vue";
import Text from "./Text.vue";

describe("Text.vue", () => {
	describe("focus()", () => {
		it("focuses the writer right after rendering", () => {
			const focus = vi.spyOn(Editor.prototype, "focus");

			const wrapper = mount(Text, {
				props: {
					content: { text: "" },
					endpoints: { field: "pages/test/fields/blocks" },
					fieldset: { name: "Text", tabs: {} },
					id: "test"
				},
				global: {
					components: { "k-writer-input": WriterInput },
					directives: { direction: {} },
					mocks: {
						$helper: {
							isComponent: (name: string) => name === "k-writer-input",
							string: {
								stripHTML: (s: string) => s,
								unescapeHTML: (s: string) => s
							},
							writer
						},
						$panel: { events: { off: vi.fn(), on: vi.fn() } },
						$t: (key: string) => key
					},
					stubs: {
						"k-writer-toolbar": {
							methods: { close: vi.fn(), open: vi.fn() },
							template: "<div />"
						}
					}
				}
			});

			// https://github.com/getkirby/kirby/issues/8762
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();

			focus.mockRestore();
		});
	});
});
