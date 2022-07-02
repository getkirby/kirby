import { ApiSetup } from "./index";

export interface ApiPagesEndpoints {
	/** Get blueprint info for page */
	blueprint: (parent: string) => Promise<object>;
	/** Get all available blueprints for page in section */
	blueprints: (parent: string, section: string) => Promise<object>;
	/** Change slug of page */
	changeSlug: (id: string, slug: string) => Promise<object>;
	/** Change status (and position) of page */
	changeStatus: (
		id: string,
		status: string,
		position: number
	) => Promise<object>;
	/** Change template of page */
	changeTemplate: (id: string, template: string) => Promise<object>;
	/** Change title of page */
	changeTitle: (id: string, title: string) => Promise<object>;
	/** Get children of page */
	children: (id: string, query?: object) => Promise<object>;
	/** Create new page */
	create: (parent: string, data: object) => Promise<object>;
	/** Delete page */
	delete: (parent: string, data: object) => Promise<object>;
	/** Duplicate page */
	duplicate: (id: string, slug: string, data: object) => Promise<object>;
	/** Get data for page */
	get: (id: string, query?: object) => Promise<object>;
	/** Get Panel-compatible ID for page */
	id: (id: string) => string;
	/** Get page's files */
	files: (id: string, query?: object) => Promise<object>;
	/** Get relative Panel path for page view */
	link: (id: string) => string;
	/** Get preview link for page */
	preview: (id: string) => Promise<object>;
	/** Search page */
	search: (parent: string, query?: object) => Promise<object>;
	/** Update page data */
	update: (parent: string, data: object) => Promise<object>;
	/** Get Panel URL for page view */
	url: (id: string, path: string) => string;
}

export default (api: ApiSetup): ApiPagesEndpoints => {
	return {
		async blueprint(parent) {
			return api.get("pages/" + this.id(parent) + "/blueprint");
		},
		async blueprints(parent, section) {
			return api.get("pages/" + this.id(parent) + "/blueprints", { section });
		},
		async changeSlug(id, slug) {
			return api.patch("pages/" + this.id(id) + "/slug", { slug });
		},
		async changeStatus(id, status, position) {
			return api.patch("pages/" + this.id(id) + "/status", {
				status,
				position
			});
		},
		async changeTemplate(id, template) {
			return api.patch("pages/" + this.id(id) + "/template", { template });
		},
		async changeTitle(id, title) {
			return api.patch("pages/" + this.id(id) + "/title", { title });
		},
		async children(id, query) {
			return api.post("pages/" + this.id(id) + "/children/search", query);
		},
		async create(parent, data) {
			if (parent === null || parent === "/") {
				return api.post("site/children", data);
			}

			return api.post("pages/" + this.id(parent) + "/children", data);
		},
		async delete(id, data) {
			return api.delete("pages/" + this.id(id), data);
		},
		async duplicate(id, slug, options) {
			return api.post("pages/" + this.id(id) + "/duplicate", {
				slug: slug,
				children: options.children || false,
				files: options.files || false
			});
		},
		async get(id, query) {
			let page = await api.get("pages/" + this.id(id), query);

			if (Array.isArray(page.content) === true) {
				page.content = {};
			}

			return page;
		},
		id(id) {
			return id.replace(/\//g, "+");
		},
		async files(id, query) {
			return api.post("pages/" + this.id(id) + "/files/search", query);
		},
		link(id) {
			return "/" + this.url(id);
		},
		async preview(id) {
			const page = await this.get(this.id(id), { select: "previewUrl" });
			return page.previewUrl;
		},
		async search(parent, query) {
			if (parent) {
				return api.post(
					"pages/" +
						this.id(parent) +
						"/children/search?select=id,title,hasChildren",
					query
				);
			}

			return api.post(
				"site/children/search?select=id,title,hasChildren",
				query
			);
		},
		async update(id, data) {
			return api.patch("pages/" + this.id(id), data);
		},
		url(id, path) {
			let url =
				id === null ? "pages" : "pages/" + String(id).replace(/\//g, "+");

			if (path) {
				url += "/" + path;
			}

			return url;
		}
	};
};
