import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UploadItem from "./UploadItem.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "icon", "theme"],
	emits: ["click"],
	template: `<button @click="$emit('click', $event)" />`
};
const Input = {
	name: "k-input",
	props: ["after", "allow", "disabled", "required", "type", "value"],
	emits: ["input"],
	template: "<input />"
};
const Preview = {
	name: "k-upload-item-preview",
	props: ["back", "color", "cover", "icon", "type", "url"],
	template: "<a />"
};
const Progress = {
	name: "k-progress",
	props: ["label", "value"],
	template: "<progress />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(UploadItem, {
		props: {
			extension: "jpg",
			id: "a",
			name: "photo",
			niceSize: "1.2 MB",
			type: "image/jpeg",
			url: "blob:a",
			...props
		},
		attrs,
		global: {
			stubs: {
				"k-button": Button,
				"k-input": Input,
				"k-progress": Progress,
				"k-upload-item-preview": Preview
			}
		}
	});
}

function input(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Input);
}

function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Button);
}

describe("k-upload-item", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "LI", "k-upload-item");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("previews the upload", () => {
			const wrapper = mount({
				back: "white",
				color: "blue-500",
				icon: "image"
			});
			expect(wrapper.findComponent(Preview).props()).toStrictEqual({
				back: "white",
				color: "blue-500",
				cover: true,
				icon: "image",
				type: "image/jpeg",
				url: "blob:a"
			});
		});

		it("lets the name be renamed as slug", () => {
			const wrapper = mount();
			expect(input(wrapper).classes()).toContain("k-upload-item-input");
			expect(input(wrapper).props()).toStrictEqual({
				after: ".jpg",
				allow: "a-z0-9@._-",
				disabled: false,
				required: true,
				type: "slug",
				value: "photo"
			});
		});

		it("shows the size", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-upload-item-meta").text()).toBe("1.2 MB");
		});
	});

	// props
	describe("completed prop", () => {
		it("marks the upload as completed", () => {
			const wrapper = mount({ completed: true });
			expect(wrapper.attributes("data-completed")).toBe("true");
			expect(input(wrapper).props("disabled")).toBe(true);
		});

		it("removes a completed upload with the check button", async () => {
			const wrapper = mount({ completed: true });
			expect(buttons(wrapper).map((button) => button.props())).toStrictEqual([
				{ disabled: undefined, icon: "check", theme: "positive" }
			]);
			await buttons(wrapper)[0].trigger("click");
			expect(wrapper.emitted("remove")).toHaveLength(1);
		});
	});

	describe("editable prop", () => {
		it("disables the name", () => {
			expect(input(mount({ editable: false })).props("disabled")).toBe(true);
		});
	});

	describe("error prop", () => {
		it("shows the error", () => {
			const wrapper = mount({ error: "Too large", progress: 50 });
			const error = wrapper.find(".k-upload-item-error");
			expect(error.text()).toBe("Too large");
			expect(error.attributes("data-theme")).toBe("negative");
			expect(wrapper.findComponent(Progress).exists()).toBe(false);
		});
	});

	describe("progress prop", () => {
		it("shows the progress", () => {
			const wrapper = mount({ progress: 40 });
			expect(
				wrapper.find(".k-upload-item-meta").text().replace(/\s+/g, " ")
			).toBe("1.2 MB - 40%");
			expect(wrapper.findComponent(Progress).props()).toStrictEqual({
				label: "upload",
				value: 40
			});
		});

		it("shows no progress before the upload starts", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Progress).exists()).toBe(false);
		});

		it("shows a loader instead of the remove button", () => {
			const wrapper = mount({ progress: 40 });
			expect(buttons(wrapper).map((button) => button.props())).toStrictEqual([
				{ disabled: true, icon: "loader", theme: undefined }
			]);
		});
	});

	describe("removable prop", () => {
		it("removes the upload with the remove button", async () => {
			const wrapper = mount();
			expect(
				buttons(wrapper).map((button) => button.props("icon"))
			).toStrictEqual(["remove"]);
			await buttons(wrapper)[0].trigger("click");
			expect(wrapper.emitted("remove")).toHaveLength(1);
		});

		it("hides the remove button", () => {
			expect(buttons(mount({ removable: false }))).toHaveLength(0);
		});
	});

	// events
	describe("events", () => {
		it("emits the new name", () => {
			const wrapper = mount();
			input(wrapper).vm.$emit("input", "portrait");
			expect(wrapper.emitted("rename")).toStrictEqual([["portrait"]]);
		});
	});
});
