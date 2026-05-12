import type Api from ".";

export default (api: Api) => ({
	async changeName(parent: string | null, filename: string, to: string) {
		return api.patch(this.url(parent, filename, "name"), {
			name: to
		});
	},
	async delete(parent: string | null, filename: string) {
		return api.delete(this.url(parent, filename));
	},
	async get(
		parent: string | null,
		filename: string,
		query?: Record<string, unknown>
	) {
		const file = await api.get<Record<string, unknown>>(
			this.url(parent, filename),
			query
		);

		if (Array.isArray(file.content) === true) {
			file.content = {};
		}

		return file;
	},
	id(id: string) {
		if (id.startsWith("/@/file/") === true) {
			return id.replace("/@/file/", "@");
		}

		if (id.startsWith("file://") === true) {
			return id.replace("file://", "@");
		}

		return id;
	},
	link(parent: string | null, filename: string, path?: string) {
		return "/" + this.url(parent, filename, path);
	},
	async update(
		parent: string | null,
		filename: string,
		data: Record<string, unknown>
	) {
		return api.patch(this.url(parent, filename), data);
	},
	url(parent: string | null, filename: string, path?: string) {
		let url = "files/" + this.id(filename);

		if (parent) {
			url = parent + "/" + url;
		}

		if (path) {
			url += "/" + path;
		}

		return url;
	}
});
