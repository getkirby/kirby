/**
 * Forces TypeScript to expand a named type alias into its full shape.
 * This makes IDE hover cards show the actual properties instead of
 * the opaque type name (e.g. `{ email: string }` instead of `UserState`).
 */
type Prettify<T> = { [K in keyof T]: T[K] } & {};

/**
 * Temporary stand-in for types not yet defined during the TypeScript migration.
 * Search for `TODO` to find all spots that still need proper types.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
type TODO = any;
