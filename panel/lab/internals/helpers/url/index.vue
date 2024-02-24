<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following url helpers in your Vue components through
				<code>this.$helper.url</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.url.base()">
			<k-text>
				<p>Returns the base URL from the &lt;base> element:</p>
				<k-code language="javascript">this.$helper.url.base(): string</k-code>
			</k-text>

			<!-- @code -->
			<h2>Result</h2>
			<k-code>{{ $helper.url.base() }}</k-code>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.buildQuery()" script="query">
			<k-text>
				<p>
					Turns the given object into an URL query string and appends it, if
					given, to the query of the origin:
				</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.buildQuery(query, origin): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ query }}</k-code>
					<br />
					<k-code>{{ origin }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.buildQuery(query, origin) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.buildUrl()" script="url">
			<k-text>
				<p>
					Builds a full URL object based on the given path or another URL object
					and query data:
				</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.buildUrl(url, query, origin): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="url" @input="url = $event" />
					<br />
					<k-code language="javascript">{{ query }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.buildUrl(url, query) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.isAbsolute()" script="url">
			<k-text>
				<p>Checks if the url string is absolute:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.isAbsolute(url): boolean</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="url" @input="url = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.isAbsolute(url) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.isSameOrigin()" script="url">
			<k-text>
				<p>Checks if the url is on the same origin:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.isSameOrigin(url): boolean</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="url" @input="url = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.isSameOrigin(url) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.isUrl()" script="url">
			<k-text>
				<p>Checks if the given argument is a URL:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.isUrl(url): boolean</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="url" @input="url = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.isUrl(url) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.makeAbsolute()" script="absolute">
			<k-text>
				<p>Make sure the URL is absolute:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.makeAbsolute(path): string</k-code>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="absolute" @input="absolute = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code>{{ $helper.url.makeAbsolute(absolute) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.url.toObject()" :code="false">
			<k-text>
				<p>Converts any given url to a URL object:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.url.toObject(url, origin): URL</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
/** @script: url */
export const url = {
	data() {
		return {
			url: "https://getkirby.com/docs"
		};
	}
};
/** @script-end */

/** @script: query */
export const query = {
	data() {
		return {
			origin: new URL("https://getkirby.com?search=kirby"),
			query: {
				foo: "bar",
				baz: ["qux", "quux"]
			}
		};
	}
};
/** @script-end */

/** @script: absolute */
export const absolute = {
	data() {
		return {
			absolute: "docs"
		};
	}
};
/** @script-end */

export default {
	mixins: [url, query, absolute]
};
</script>
