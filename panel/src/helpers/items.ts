/**
 * Item props requested in the same tick are collected
 * into a batch and sent as one request:
 *
 * 1. items() calls item() for each id
 * 2. item() adds the id to the batch for its endpoint
 *    and options
 * 3. Batch.add() waits via queueMicrotask() for the
 *    end of the tick
 * 4. Batch.send() takes the collected ids and splits
 *    them into chunks (if needed)
 * 5. Batch.sendChunk() gets called for each chunk, sends
 *    request to endpoint
 * 6. Batch.resolve() gets called for each id individually,
 *    hands over item props to the initial caller
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */

/** Item props from the response */
type Item = Record<string, unknown>;
/** Request options passed on to the endpoint */
type Query = Record<string, unknown>;

/**
 * One batch per endpoint and options
 */
const batches = new Map<string, Batch>();

/**
 * Max number of ids per request: ids are added to the
 * query string, we must ensure the URL does not get too long
 */
const LIMIT = 100;

/**
 * Collects ids by endpoint and options,
 * in the two states they can be in:
 * ids waiting to be sent and items waiting to come back.
 */
class Batch {
	endpoint: string;
	ids: string[] = [];
	items = new Map<string, PromiseWithResolvers<Item | undefined>>();
	key: string;
	query: Query;

	constructor(endpoint: string, key: string, query: Query) {
		this.endpoint = endpoint;
		this.key = key;
		this.query = query;
	}

	/**
	 * Adds a single id to the batch and returns
	 * the item props once the promise resolves
	 */
	add(id: string): Promise<Item | undefined> {
		// check if a response for the same item is already pending;
		// so that it can join the request and not create its redundant own
		const existing = this.items.get(id);

		if (existing !== undefined) {
			return existing.promise;
		}

		// create and track the promise in the batch:
		// we return the promise here, but resolve it with the
		// props data later in `resolve()`
		const pending = Promise.withResolvers<Item | undefined>();

		this.items.set(id, pending);
		this.ids.push(id);

		// for a fresh batch start the timer to send the request
		// once the current tick has passed;
		// in the meantime more ids can join the batch
		if (this.ids.length === 1) {
			queueMicrotask(() => this.send());
		}

		return pending.promise;
	}

	/**
	 * Returns the batch for an endpoint and its options
	 * and creates it, if it does not exist yet
	 */
	static for(endpoint: string, query: Query): Batch {
		// sort query, so that same options share a batch, no matter the order
		const key =
			endpoint + "/" + JSON.stringify(query, Object.keys(query).sort());
		let batch = batches.get(key);

		if (batch === undefined) {
			batch = new Batch(endpoint, key, query);
			batches.set(key, batch);
		}

		return batch;
	}

	/**
	 * Hands the item to the caller and drops it from the batch,
	 * so that the next call starts a fresh request
	 */
	resolve(id: string, item: Item | undefined): void {
		this.items.get(id)?.resolve(item);
		this.items.delete(id);

		if (this.ids.length === 0 && this.items.size === 0) {
			batches.delete(this.key);
		}
	}

	/**
	 * Sends the collected ids as one or more requests/chunks and
	 * hands each caller the item for its own id
	 */
	async send(): Promise<void> {
		// take the collected ids right away, so that new calls added while
		// this request is in flight will collect a batch of their own
		const ids = this.ids;
		this.ids = [];

		const requests = [];

		for (let index = 0; index < ids.length; index += LIMIT) {
			requests.push(this.sendChunk(ids.slice(index, index + LIMIT)));
		}

		await Promise.all(requests);
	}

	/**
	 * Sends one chunk of ids to the endpoint. The batch supplies
	 * the endpoint, the options and the waiting callers.
	 */
	async sendChunk(chunk: string[]): Promise<void> {
		try {
			const response = (await window.panel.get(this.endpoint, {
				query: {
					...this.query,
					items: chunk.join(",")
				}
			})) as { items?: (Item | null)[] };

			for (const [index, id] of chunk.entries()) {
				this.resolve(id, response.items?.[index] ?? undefined);
			}
		} catch (error) {
			// hand the error to the Panel, so that an expired session,
			// a redirect or a lost connection is still acted upon
			window.panel.error(error);

			// a failed request resolves to nothing, just like an unknown id
			for (const id of chunk) {
				this.resolve(id, undefined);
			}
		}
	}
}

/**
 * Adds a single id to its matching batch and
 * returns the item props once the promise resolves
 */
function item(
	endpoint: string,
	id: string,
	query: Query
): Promise<Item | undefined> {
	// the backend drops blank ids when it splits the query string,
	// which would shift the response for every id after them;
	// checked before the batch, so that a blank id never creates one
	if (id?.trim() === "" || id === null || id === undefined) {
		return Promise.resolve(undefined);
	}

	return Batch.for(endpoint, query).add(id);
}

/**
 * Request props for items by model id. Calls from the same tick
 * share a request and a call for an item that is already on its
 * way is joined instead of being requested again.
 *
 * @example
 * const item = await items("items/files", "file://abc");
 * const list = await items("items/pages", ["page://a", "page://b"]);
 */
export default function items(
	endpoint: string,
	id: string,
	query?: Query
): Promise<Item | undefined>;
export default function items(
	endpoint: string,
	ids: string[],
	query?: Query
): Promise<(Item | undefined)[]>;
export default function items(
	endpoint: string,
	ids: string | string[],
	query: Query = {}
): Promise<Item | undefined> | Promise<(Item | undefined)[]> {
	if (Array.isArray(ids) === true) {
		return Promise.all(ids.map((id) => item(endpoint, id, query)));
	}

	return item(endpoint, ids, query);
}
