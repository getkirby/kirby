/**
 * Forces TypeScript to expand a named type alias into its full shape.
 * This makes IDE hover cards show the actual properties instead of
 * the opaque type name (e.g. `{ email: string }` instead of `UserState`).
 */
type Prettify<T> = { [K in keyof T]: T[K] } & {};
