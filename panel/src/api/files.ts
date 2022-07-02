import { ApiInterface } from "./index";

export interface ApiFilesEndpoints {
	/** Change name of file */
	changeName: (parent: string, filename: string, to: string) => Promise<object>;
	/** Delete file */
	delete: (parent: string, filename: string) => Promise<object>;
	/** Get data for file */
	get: (parent: string, filename: string, query?: object) => Promise<object>;
	/** Get relative Panel path for file view */
	link: (parent: string, filename: string, path: string) => string;
	/** Update file's content data */
	update: (parent: string, filename: string, data: object) => Promise<object>;
	/** Get Panel URL for file view */
	url: (parent: string, filename: string, path: string) => string;
}

export default (api: Partial<ApiInterface>): ApiFilesEndpoints => {
	return {
		async changeName(parent, filename, to) {
			return api.patch(parent + "/files/" + filename + "/name", {
				name: to
			});
		},
		async delete(parent, filename) {
			return api.delete(parent + "/files/" + filename);
		},
		async get(parent, filename, query) {
			const file = await api.get(parent + "/files/" + filename, query);

			if (Array.isArray(file.content) === true) {
				file.content = {};
			}

			return file;
		},
		link(parent, filename, path) {
			return "/" + this.url(parent, filename, path);
		},
		async update(parent, filename, data) {
			return api.patch(parent + "/files/" + filename, data);
		},
		url(parent, filename, path) {
			let url = parent + "/files/" + filename;

			if (path) {
				url += "/" + path;
			}

			return url;
		}
	};
};
