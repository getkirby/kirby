<template>
	<k-lab-examples>
		<k-text>
			Access the following array helpers in your Vue components through
			<code>this.$helpers.array</code>
		</k-text>

		<k-lab-example label="$helpers.array.fromObject()">
			<k-text>
				<p>
					Creates an array from an object:
					<code>this.$helpers.array.fromObject(object)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ fromObjectInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.fromObject(fromObjectInput)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.array.search()">
			<k-text>
				<p>
					Filters an array by a provided query:
					<code>
						this.$helpers.array.search(array, "{{ searchQuery }}", { min: 2,
						field: "name" })
					</code>
				</p>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column>
					<k-input
						type="text"
						placeholder="Query"
						@input="searchQuery = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ searchInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ searchResult }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.array.sortBy()">
			<k-text>
				<p>
					Sorts an array by one or more fields and directions:
					<code>this.$helpers.array.sortBy(array, "name desc")</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ searchInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.sortBy(searchInput, "name desc")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.array.split()">
			<k-text>
				<p>
					Splits an array into groups by a delimiter entry:
					<code>this.$helpers.array.split(array, "-")</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ splitInput }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.split(splitInput, "-")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.array.wrap()">
			<k-text>
				<p>
					Wraps a value in an array (ensures the value will be an array):
					<code>this.$helpers.array.wrap(value)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">"aaa"</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ $helper.array.wrap("aaa") }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{{ ["aaa"] }}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.array.wrap(["aaa"])
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			searchQuery: ""
		};
	},
	computed: {
		fromObjectInput() {
			return { a: 1, b: 2, c: 3 };
		},
		searchInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 2, name: "Homer Simpson" },
				{ id: 3, name: "Jane Doe" }
			];
		},
		searchResult() {
			return this.$helper.array.search(this.searchInput, this.searchQuery, {
				min: 0,
				field: "name"
			});
		},
		splitInput() {
			return [
				{ id: 1, name: "John Doe" },
				{ id: 3, name: "Jane Doe" },
				"-",
				{ id: 2, name: "Homer Simpson" }
			];
		}
	}
};
</script>

<style>
.k-lab-example .k-text {
	margin-bottom: var(--spacing-6);
}
.k-lab-example h2 {
	margin-bottom: var(--spacing-3);
	font-weight: var(--font-bold);
}
</style>
