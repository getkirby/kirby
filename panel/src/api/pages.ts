import type Api from ".";

export default (api: Api) => ({
	async blueprint(parent: string) {
		return api.get("pages/" + this.id(parent) + "/blueprint");
	},
	async blueprints(parent: string, section: string) {
		return api.get("pages/" + this.id(parent) + "/blueprints", {
			section: section
		});
	},
	async changeSlug(id: string, slug: string) {
		return api.patch("pages/" + this.id(id) + "/slug", { slug });
	},
	async changeStatus(id: string, status: string, position?: number) {
		return api.patch("pages/" + this.id(id) + "/status", {
			status: status,
			position: position
		});
	},
	async changeTemplate(id: string, template: string) {
		return api.patch("pages/" + this.id(id) + "/template", {
			template: template
		});
	},
	async changeTitle(id: string, title: string) {
		return api.patch("pages/" + this.id(id) + "/title", { title });
	},
	async children(id: string, query?: Record<string, unknown>) {
		return api.post("pages/" + this.id(id) + "/children/search", query);
	},
	async create(parent: string | null, data: Record<string, unknown>) {
		if (parent === null || parent === "/") {
			return api.post("site/children", data);
		}

		return api.post("pages/" + this.id(parent) + "/children", data);
	},
	async delete(id: string, data: Record<string, unknown>) {
		return api.delete("pages/" + this.id(id), data);
	},
	async duplicate(
		id: string,
		slug: string,
		options: { children?: boolean; files?: boolean } = {}
	) {
		return api.post("pages/" + this.id(id) + "/duplicate", {
			slug: slug,
			children: options.children ?? false,
			files: options.files ?? false
		});
	},
	async get(id: string, query?: Record<string, unknown>) {
		const page = await api.get<Record<string, unknown>>(
			"pages/" + this.id(id),
			query
		);

		if (Array.isArray(page.content) === true) {
			page.content = {};
		}

		return page;
	},
	id(id: string) {
		if (/^\/(.*\/)?@\/page\//.test(id) === true) {
			return id.replace(/^\/(.*\/)?@\/page\//, "@");
		}

		if (id.startsWith("page://") === true) {
			return id.replace("page://", "@");
		}

		return id.replace(/\//g, "+");
	},
	async files(id: string, query?: Record<string, unknown>) {
		return api.post("pages/" + this.id(id) + "/files/search", query);
	},
	link(id: string) {
		return "/" + this.url(id);
	},
	async preview(id: string) {
		const page = await this.get(this.id(id), { select: "previewUrl" });
		return page.previewUrl;
	},
	async search(parent: string | null, query?: Record<string, unknown>) {
		if (parent) {
			return api.post(
				"pages/" +
					this.id(parent) +
					"/children/search?select=id,title,hasChildren",
				query
			);
		}

		return api.post("site/children/search?select=id,title,hasChildren", query);
	},
	async update(id: string, data: Record<string, unknown>) {
		return api.patch("pages/" + this.id(id), data);
	},
	url(id: string | null, path?: string) {
		let url = id === null ? "pages" : "pages/" + String(id).replace(/\//g, "+");

		if (path) {
			url += "/" + path;
		}

		return url;
	}
});
