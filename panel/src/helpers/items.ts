/**
 * Item props request from the same tick are collected
 * into a batch and sent as one request:
 *
 * 1. items() calls queueItem() for each id
 * 2. queueItem() adds the id to the queue for its endpoint
 *    and options
 * 3. Queue waits via queueMicrotask() for the end of tick
 * 4. sendQueue() takes the queued ids and splits them
 *    into chunks (if needed)
 * 5. sendChunk() gets called for each chunk, sends
 *    request to endpoint
 * 6. resolveItem() gets called for each id individually,
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
 * Collects ids by endpoint and options,
 * in the two states they can be in:
 * ids waiting to be sent and items waiting to come back.
 */
type Queue = {
	endpoint: string;
	ids: string[];
	items: Map<string, PromiseWithResolvers<Item | undefined>>;
	key: string;
	query: Query;
};

/**
 * One queue per endpoint and options
 */
const queues = new Map<string, Queue>();

/**
 * Returns the queue for an endpoint and its options
 * and creates it, if it does not exist yet
 */
function getQueue(endpoint: string, query: Query): Queue {
	// sort query, so that same options share a queue, no matter the order
	const key = endpoint + "/" + JSON.stringify(query, Object.keys(query).sort());
	let queue = queues.get(key);

	if (queue === undefined) {
		queue = { endpoint, ids: [], items: new Map(), key, query };
		queues.set(key, queue);
	}

	return queue;
}

/**
 * Adds a single id to its matching queue and
 * returns the item props once the promise resolves
 */
function queueItem(
	endpoint: string,
	id: string,
	query: Query
): Promise<Item | undefined> {
	// the backend drops blank ids when it splits the query string,
	// which would shift the response for every id after them
	if (id?.trim() === "" || id === null || id === undefined) {
		return Promise.resolve(undefined);
	}

	// get the right queue for the item and check
	// if a response for the same item is already pending;
	// so that it can join the request and not create its redundant own
	const queue = getQueue(endpoint, query);
	const existing = queue.items.get(id);

	if (existing !== undefined) {
		return existing.promise;
	}

	// create and track the promise in the queue:
	// we return the promise here, but resolve it with the
	// props data later in `resolveItem()`
	const item = Promise.withResolvers<Item | undefined>();

	queue.items.set(id, item);
	queue.ids.push(id);

	// for a newly created queue start the timer to send
	// the request once the current tick has passed;
	// in the meantime more ids can join the queue
	if (queue.ids.length === 1) {
		queueMicrotask(() => sendQueue(queue));
	}

	return item.promise;
}

/**
 * Hands the item to the caller and drops it from the queue,
 * so that the next call starts a fresh request
 */
function resolveItem(queue: Queue, id: string, item: Item | undefined): void {
	queue.items.get(id)?.resolve(item);
	queue.items.delete(id);

	if (queue.ids.length === 0 && queue.items.size === 0) {
		queues.delete(queue.key);
	}
}

/**
 * Sends one chunk of ids to the endpoint. The queue supplies
 * the endpoint, the options and the waiting callers.
 */
async function sendChunk(queue: Queue, chunk: string[]): Promise<void> {
	try {
		const response = (await window.panel.get(queue.endpoint, {
			query: {
				...queue.query,
				items: chunk.join(",")
			}
		})) as { items?: (Item | null)[] };

		for (const [index, id] of chunk.entries()) {
			resolveItem(queue, id, response.items?.[index] ?? undefined);
		}
	} catch (error) {
		// hand the error to the Panel, so that an expired session,
		// a redirect or a lost connection is still acted upon
		window.panel.error(error);

		// a failed request resolves to nothing, just like an unknown id
		for (const id of chunk) {
			resolveItem(queue, id, undefined);
		}
	}
}

/**
 * Sends the queued ids as one or more requests/chunks and
 * hands each caller the item for its own id
 */
async function sendQueue(queue: Queue): Promise<void> {
	// take the queued ids right away, so that new calls added while
	// this request is in flight will collect their own new batch
	const batch = queue.ids;
	queue.ids = [];

	const requests = [];

	// max number of ids per request:
	// ids are added to the query string,
	// we must ensure the URL does not get too long
	const limit = 100;

	for (let index = 0; index < batch.length; index += limit) {
		const chunk = batch.slice(index, index + limit);
		requests.push(sendChunk(queue, chunk));
	}

	await Promise.all(requests);
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
		return Promise.all(ids.map((id) => queueItem(endpoint, id, query)));
	}

	return queueItem(endpoint, ids, query);
}
