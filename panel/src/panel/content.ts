import { isObject, length } from "@/helpers/object";
import { reactive } from "vue";
import throttle from "@/helpers/throttle";
import type Dialog from "./dialog.js";

type Env = {
	api: string;
	language: string | null;
};

type Lock = {
	isLocked: boolean;
	modified: Date | null;
};

type LockError = Error & {
	details: Lock;
	key: string;
};

type RequestMethod = "discard" | "publish" | "save";

type RequestOptions = {
	// null headers are removed again in `api.request`
	headers: Record<string, string | null>;
	signal?: AbortSignal;
	silent?: boolean;
};

type Values = Record<string, unknown>;

type VersionId = "changes" | "latest";

/**
 * Whether the error has been caused by a lock from another user
 *
 * TODO: narrow this down to `RequestError` as soon as the class
 * exposes the error key from the response
 */
const isLockError = (error: unknown): error is LockError =>
	typeof (error as LockError)?.key === "string" &&
	(error as LockError).key.startsWith("error.content.lock");

/**
 * @since 5.0.0
 */
export default function Content(panel: TODO) {
	const content = reactive({
		/**
		 * Cancel any scheduled or ongoing save requests
		 */
		cancelSaving(): void {
			// cancel any scheduled save requests
			this.saveLazy.cancel();

			// ensure to abort unfinished previous save request
			// to avoid race conditions with older content
			this.saveAbortController?.abort();
		},

		dialog: null as ReturnType<typeof Dialog> | null,

		/**
		 * Returns an object with all changed fields
		 */
		diff(env?: Partial<Env>): Values {
			// changes can only be computed for the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot get changes for another view");
			}

			const versions = this.versions();
			const diff: Values = {};

			for (const field in versions.changes) {
				const changed = JSON.stringify(versions.changes[field]);
				const original = JSON.stringify(versions.latest[field]);

				if (changed !== original) {
					diff[field] = versions.changes[field];
				}
			}

			// find all fields that have been present in the original content
			// but have been removed from the current content
			for (const field in versions.latest) {
				if (versions.changes[field] === undefined) {
					diff[field] = null;
				}
			}

			return diff;
		},

		/**
		 * Removes all unpublished changes
		 */
		async discard(env?: Partial<Env>): Promise<void> {
			if (this.isProcessing === true) {
				return;
			}

			// Only discard changes from the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot discard content from another view");
			}

			// Check the lock state to determine if we can discard
			if (this.isLocked(env) === true) {
				throw new Error("Cannot discard locked changes");
			}

			// Cancel any ongoing save requests.
			// The discard request will throw those
			// changes away anyway.
			this.cancelSaving();

			// Start processing the request
			this.isProcessing = true;

			try {
				await this.request("discard", {}, env);

				// update the props for the current view
				this.versions().changes = this.version("latest");

				this.emit("discard", {}, env);
			} catch (error) {
				// handle locked states
				if (isLockError(error) === true) {
					return this.lockDialog(error.details);
				}

				// let our regular error handler take over
				throw error;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Emit a custom content event
		 * and add the api and language properties
		 */
		emit(event: string, options: Values = {}, env?: Partial<Env>): void {
			panel.events.emit("content." + event, {
				...options,
				...this.env(env)
			});
		},

		/**
		 * Ensure a consistent environment object
		 * with api and language properties
		 */
		env(env: Partial<Env> = {}): Env {
			return {
				api: panel.view.props.api,
				language: panel.language.code,
				...env
			};
		},

		/**
		 * Whether there are any changes
		 */
		hasDiff(env?: Partial<Env>): boolean {
			return length(this.diff(env)) > 0;
		},

		/**
		 * Whether the api endpoint belongs to the current view
		 */
		isCurrent(env?: Partial<Env>): boolean {
			const given = this.env(env);
			const current = this.env();
			return current.api === given.api && current.language === given.language;
		},

		/**
		 * Whether the current view is locked
		 */
		isLocked(env?: Partial<Env>): boolean {
			return this.lock(env)?.isLocked ?? false;
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 */
		isProcessing: false,

		/**
		 * Get the lock state for the current view
		 */
		lock(env?: Partial<Env>): Lock {
			if (this.isCurrent(env) === false) {
				throw new Error(
					"The lock state cannot be detected for content from another view"
				);
			}

			return panel.view.props.lock;
		},

		/**
		 * Opens the lock dialog to inform the current editor
		 * about edits from another user
		 */
		lockDialog(lock: Lock): void {
			this.dialog = panel.dialog;
			this.dialog!.open({
				component: "k-lock-alert-dialog",
				props: {
					lock: lock
				},
				on: {
					close: () => {
						this.dialog = null;
						panel.view.reload();
					}
				}
			});
		},

		/**
		 * Merge new content changes with the
		 * original values and update the view props
		 */
		merge(values: Values = {}, env?: Partial<Env>): Values {
			if (this.isCurrent(env) === false) {
				throw new Error("The content in another view cannot be merged");
			}

			if (isObject(values) === false) {
				values = {};
			}

			return (this.versions().changes = {
				...this.version("changes"),
				...values
			});
		},

		/**
		 * Publishes any changes
		 */
		async publish(values: Values = {}, env?: Partial<Env>): Promise<void> {
			if (this.isProcessing === true) {
				return;
			}

			if (this.isCurrent(env) === false) {
				throw new Error("Cannot publish content from another view");
			}

			// Cancel any ongoing save requests.
			// The publish request will submit the
			// latest state of the form again.
			this.cancelSaving();

			// Start processing the request
			this.isProcessing = true;

			// Send updated values to API
			try {
				await this.request("publish", this.merge(values, env), env);

				// close the dialog if it is still open
				this.dialog?.close();

				// update the props for the current view
				this.versions().latest = this.version("changes");

				this.emit("publish", { values }, env);
			} catch (error) {
				// handle locked states
				if (isLockError(error) === true) {
					return this.lockDialog(error.details);
				}

				throw error;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Simplified request handler for all content API requests
		 */
		async request(
			method: RequestMethod = "save",
			values: Values = {},
			env?: Partial<Env>
		): Promise<void> {
			const { api, language } = this.env(env);

			const options: RequestOptions = {
				headers: {
					"x-language": language
				}
			};

			if (method === "save") {
				options.signal = this.saveAbortController?.signal;
				options.silent = true;
			}

			return panel.api.post(api + "/changes/" + method, values, options);
		},

		/**
		 * Saves any changes
		 *
		 * @returns Whether the changes have been written
		 */
		async save(values: Values = {}, env?: Partial<Env>): Promise<boolean> {
			// ensure to abort unfinished previous save request
			// to avoid race conditions with older content
			this.cancelSaving();

			// create a new abort controller
			this.saveAbortController = new AbortController();

			try {
				await this.request("save", values, env);

				// close the dialog if it is still open
				this.dialog?.close();

				// update the lock timestamp
				if (this.isCurrent(env) === true) {
					this.lock(env).modified = new Date();
				}

				this.emit("save", { values }, env);

				return true;
			} catch (error) {
				// handle aborted requests silently. A newer save request
				// has taken over and will write the latest state instead.
				if (error instanceof Error && error.name === "AbortError") {
					return false;
				}

				// processing must not be interrupted for aborted
				// requests because the follow-up request is already
				// in progress and setting the state to false here
				// would be wrong
				this.isProcessing = false;

				// handle locked states
				if (isLockError(error) === true) {
					this.lockDialog(error.details);
					return false;
				}

				throw error;
			}
		},

		/**
		 * @internal
		 */
		saveAbortController: null as AbortController | null,

		/**
		 * Placeholder for the throttled save method
		 * that gets added at the end of the file
		 */
		saveLazy: undefined! as ReturnType<
			typeof throttle<[Values?, Partial<Env>?]>
		>,

		/**
		 * Releases the content lock without discarding changes.
		 * Called when the editor navigates away from the view.
		 */
		async unlock(env?: Partial<Env>): Promise<void> {
			// persist any pending changes before releasing the lock, so that
			// the changes cannot be dropped or the lock left behind
			// due to a save that finishes after the unlock request.
			// changes can only be detected for the current view
			if (this.isCurrent(env) === true && this.hasDiff(env) === true) {
				// abort the unlock when the changes could not be written:
				// the view got locked in the meantime or a newer save
				// request took over. Staying on the current view keeps
				// both the changes and the lock, so nothing is lost and
				// the call can simply be repeated
				if ((await this.update({}, env)) !== true) {
					throw new Error("The changes could not be saved before unlocking");
				}
			}

			// fail silently because the lock will be released after the configured timeout
			return this.unlockPostRequest(env).catch(() => {});
		},

		/**
		 * Sends the unlock request for the given view.
		 * Use sendBeacon for reliability during page unload. Browsers
		 * guarantee delivery even when the page is being closed.
		 */
		unlockBeaconRequest(env?: Partial<Env>): void {
			const { api, language } = this.env(env);

			this.cancelSaving();

			// Build the URL with csrf and language as query params.
			// sendBeacon cannot set custom headers.
			const url = panel.url(`${panel.api.endpoint}${api}/changes/unlock`, {
				csrf: panel.api.csrf,
				language: language
			});

			// sendBeacon returns true if the request was successfully queued.
			if (navigator.sendBeacon(url) === true) {
				return;
			}

			// Fall back to a regular request if sendBeacon wasn't queued.
			// Fail silently to avoid blocking the unload event
			this.unlockPostRequest(env).catch(() => {});
		},

		/**
		 * Sends the unlock request for the given view
		 * as a regular API request
		 */
		async unlockPostRequest(env?: Partial<Env>): Promise<void> {
			const { api, language } = this.env(env);

			this.cancelSaving();

			return panel.api.post(
				api + "/changes/unlock",
				{},
				{
					headers: { "x-language": language },
					silent: true
				}
			);
		},

		/**
		 * Updates the form values of the current view
		 *
		 * @returns Whether the changes have been written
		 */
		async update(values: Values = {}, env?: Partial<Env>): Promise<boolean> {
			return await this.save(this.merge(values, env), env);
		},

		/**
		 * Updates the form values of the current view with a delay
		 */
		updateLazy(values: Values = {}, env?: Partial<Env>): void {
			this.saveLazy(this.merge(values, env), env);
		},

		/**
		 * Returns a specific version of the content
		 */
		version(versionId: VersionId): Values {
			return this.versions()[versionId];
		},

		/**
		 * Returns all versions of the content
		 */
		versions(): Record<VersionId, Values> {
			return panel.view.props.versions;
		}
	});

	// create a delayed version of save
	// that we can use in the input event
	content.saveLazy = throttle(content.save, 1000, {
		leading: true,
		trailing: true
	});

	return content;
}
