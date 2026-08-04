/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */

type Item = Record<string, unknown>;
type Query = Record<string, unknown>;
type Batch = {
	endpoint: string;
	ids: string[];
	key: string;
	query: Query;
	resolvers: Map<string, (item: Item | undefined) => void>;
};

/**
 * Max number of ids per request. The ids travel in the query
 * string, so a large batch needs to be split before the url gets
 * too long. A plain count is a rough stand-in for the actual
 * length, which is fine as long as ids stay reasonably short.
 */
const LIMIT = 100;

/**
 * Pending batches, keyed by endpoint and options.
 * Calls can only share a request when they ask
 * the backend for the same representation.
 */
const batches = new Map<string, Batch>();

/**
 * Lookups that are already on their way,
 * keyed by representation and id. They are
 * dropped as soon as the request resolved,
 * so that no item is kept around.
 */
const pending = new Map<string, Promise<Item | undefined>>();

/**
 * Sends the collected batch and hands
 * each caller the item for its own id
 */
async function flush(key: string): Promise<void> {
	const batch = batches.get(key);

	if (batch === undefined) {
		return;
	}

	// drop the batch right away, so that new calls added while this
	// request is in flight will start their own new batch
	batches.delete(key);

	const chunks = [];

	for (let index = 0; index < batch.ids.length; index += LIMIT) {
		chunks.push(request(batch, batch.ids.slice(index, index + LIMIT)));
	}

	await Promise.all(chunks);
}

/**
 * Adds a single id to its batch and returns the promise
 * that resolves once the batch has been flushed
 */
function item(
	endpoint: string,
	id: string,
	query: Query
): Promise<Item | undefined> {
	const key = endpoint + "/" + normalize(query);
	const lookup = key + "/" + id;
	const existing = pending.get(lookup);

	// join a request for the same item that is already on its way,
	// no matter if it is still collecting or already in flight
	if (existing !== undefined) {
		return existing;
	}

	let batch = batches.get(key);

	if (batch === undefined) {
		batch = { endpoint, ids: [], key, query, resolvers: new Map() };
		batches.set(key, batch);

		// flush once the current tick has queued all its lookups
		queueMicrotask(() => flush(key));
	}

	const { promise, resolve } = Promise.withResolvers<Item | undefined>();

	batch.ids.push(id);
	batch.resolvers.set(id, resolve);
	pending.set(lookup, promise);

	return promise;
}

/**
 * Sorts the query, so that the same options
 * share a batch, no matter in which order
 * the caller passed them
 */
function normalize(query: Query): string {
	const sorted: Query = {};

	for (const name of Object.keys(query).sort()) {
		sorted[name] = query[name];
	}

	return JSON.stringify(sorted);
}

/**
 * Requests a single chunk of ids from the endpoint
 */
async function request(batch: Batch, ids: string[]): Promise<void> {
	try {
		const response = (await window.panel.get(batch.endpoint, {
			query: {
				...batch.query,
				items: ids.join(",")
			}
		})) as { items?: (Item | null)[] };

		for (const [index, id] of ids.entries()) {
			settle(batch, id, response.items?.[index] ?? undefined);
		}
	} catch {
		// a failed lookup resolves to nothing, just like an unknown id
		for (const id of ids) {
			settle(batch, id, undefined);
		}
	}
}

/**
 * Hands the item to the caller and clears the lookup,
 * so that the next call starts a fresh request
 */
function settle(batch: Batch, id: string, item: Item | undefined): void {
	pending.delete(batch.key + "/" + id);
	batch.resolvers.get(id)?.(item);
}

/**
 * Request props for items by model id. Calls from the same tick
 * share a request and a lookup that is already on its way is
 * joined instead of being requested again.
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
