import { reactive } from "vue";
import RequestError from "@/errors/RequestError";
import { isAbortError } from "@/helpers/error";
import { isObject, length } from "@/helpers/object";
import throttle from "@/helpers/throttle";
import Dialog from "./dialog";

type Env = {
	api: string;
	language: string;
};

type Lock = {
	isLocked: boolean;
	modified: Date;
};

type VersionId = "latest" | "changes";

const isLockRequestError = (
	error: unknown
): error is RequestError & { details: Lock } => {
	return (
		error instanceof RequestError &&
		error.key?.startsWith("error.content.lock") === true
	);
};

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

		dialog: undefined as ReturnType<typeof Dialog> | undefined,

		/**
		 * Returns an object with all changed fields
		 */
		diff(env?: Partial<Env>): Record<string, unknown> {
			// changes can only be computed for the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot get changes for another view");
			}

			const versions = this.versions();
			const diff: Record<string, unknown> = {};

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
				panel.view.props.versions.changes = this.version("latest");

				this.emit("discard", {}, env);
			} catch (error) {
				// handle locked states
				if (isLockRequestError(error) === true) {
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
		emit(
			event: string,
			options: Record<string, unknown> = {},
			env?: Partial<Env>
		): void {
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
		 * Updates the lock's modified timestamp after a successful save
		 */
		renewLock(env?: Partial<Env>): void {
			this.lock(env).modified = new Date();
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
						this.dialog = undefined;
						panel.view.reload();
					}
				}
			});
		},

		/**
		 * Merge new content changes with the
		 * original values and update the view props
		 */
		merge(
			values: Record<string, unknown> = {},
			env?: Partial<Env>
		): Record<string, unknown> {
			if (this.isCurrent(env) === false) {
				throw new Error("The content in another view cannot be merged");
			}

			if (isObject(values) === false) {
				values = {};
			}

			panel.view.props.versions.changes = {
				...this.version("changes"),
				...values
			};

			return panel.view.props.versions.changes;
		},

		/**
		 * Publishes any changes
		 */
		async publish(
			values: Record<string, unknown> = {},
			env?: Partial<Env>
		): Promise<void> {
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
				panel.view.props.versions.latest = this.version("changes");

				this.emit("publish", { values }, env);
			} catch (error) {
				// handle locked states
				if (isLockRequestError(error) === true) {
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
			method: "discard" | "publish" | "save" = "save",
			values: Record<string, unknown> = {},
			env?: Partial<Env>
		): Promise<void> {
			const { api, language } = this.env(env);

			const options: RequestInit & { silent?: boolean } = {
				headers: {
					"x-language": language
				}
			};

			if (method === "save") {
				options.signal = this.saveAbortController?.signal;
				options.silent = true;
			}

			panel.api.post(api + "/changes/" + method, values, options);
		},

		/**
		 * Saves any changes
		 */
		async save(
			values: Record<string, unknown> = {},
			env?: Partial<Env>
		): Promise<void> {
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
					this.renewLock(env);
				}

				this.emit("save", { values }, env);
			} catch (error) {
				// handle aborted requests silently
				if (isAbortError(error) === true) {
					return;
				}

				// processing must not be interrupted for aborted
				// requests because the follow-up request is already
				// in progress and setting the state to false here
				// would be wrong
				this.isProcessing = false;

				// handle locked states
				if (isLockRequestError(error) === true) {
					return this.lockDialog(error.details);
				}

				throw error;
			}
		},

		/**
		 * Placeholder for throttled function that gets added
		 * at the end of the file
		 */
		saveLazy: undefined! as ReturnType<
			typeof throttle<[Record<string, unknown>?, Partial<Env>?]>
		>,

		/**
		 * @internal
		 */
		saveAbortController: undefined as AbortController | undefined,

		/**
		 * Releases the content lock without discarding changes.
		 * Called when the editor navigates away from the view.
		 */
		unlock(env?: Partial<Env>): void {
			// Cancel any pending saves first to avoid race conditions
			this.cancelSaving();

			const { api, language } = this.env(env);

			// Build the URL with csrf and language as query params.
			// sendBeacon cannot set custom headers.
			const url = panel.url(`${panel.api.endpoint}${api}/changes/unlock`, {
				csrf: panel.api.csrf,
				language: language
			});

			// Use sendBeacon for reliability during page unload. Browsers
			// guarantee delivery even when the page is being closed.
			// Returns true if the request was successfully queued.
			if (navigator.sendBeacon(url) === true) {
				return;
			}

			// Fall back to a regular request if sendBeacon wasn't queued
			panel.api
				.post(
					api + "/changes/unlock",
					{},
					{
						headers: { "x-language": language },
						silent: true
					}
				)
				.catch(() => {
					// Silently ignore errors. The lock will expire after 10 minutes anyway.
				});
		},

		/**
		 * Updates the form values of the current view
		 */
		async update(
			values: Record<string, unknown> = {},
			env?: Partial<Env>
		): Promise<void> {
			return await this.save(this.merge(values, env), env);
		},

		/**
		 * Updates the form values of the current view with a delay
		 */
		updateLazy(values: Record<string, unknown> = {}, env?: Partial<Env>): void {
			this.saveLazy(this.merge(values, env), env);
		},

		/**
		 * Returns a specific version of the content
		 */
		version(versionId: VersionId): Record<string, unknown> {
			return this.versions()[versionId];
		},

		/**
		 * Returns all versions of the content
		 */
		versions(): Record<VersionId, Record<string, unknown>> {
			return panel.view.props.versions;
		}
	});

	// create a delayed version of save
	// that we can use in the input event
	content.saveLazy = throttle(content.save, 500, {
		leading: true,
		trailing: true
	});

	return content;
}
