export default (api) => ({
	async changeName(parent, filename, to) {
		return api.patch(this.url(parent, filename, "name"), {
			name: to
		});
	},
	async delete(parent, filename) {
		return api.delete(this.url(parent, filename));
	},
	async get(parent, filename, query) {
		let file = await api.get(this.url(parent, filename), query);

		if (Array.isArray(file.content) === true) {
			file.content = {};
		}

		return file;
	},
	id(id) {
		if (id.startsWith("/@/file/") === true) {
			return id.replace("/@/file/", "@");
		}

		if (id.startsWith("file://") === true) {
			return id.replace("file://", "@");
		}

		return id;
	},
	link(parent, filename, path) {
		return "/" + this.url(parent, filename, path);
	},
	async update(parent, filename, data) {
		return api.patch(this.url(parent, filename), data);
	},
	url(parent, filename, path) {
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
