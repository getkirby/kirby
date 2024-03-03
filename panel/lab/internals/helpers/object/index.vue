<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following object helpers in your Vue components through
				<code>this.$helper.object</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.object.isEmpty()" :code="false">
			<k-text>
				<p>Checks if value is empty:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.isEmpty(value): boolean</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">""</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.object.isEmpty("")
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">null</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty(null)
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">undefined</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty(undefined)
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty({})
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{ foo: "bar" }</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty({ foo: "bar" })
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">[]</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty([])
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">["foo", "bar"]</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isEmpty(["foo", "bar"])
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="$helper.object.isObject()" :code="false">
			<k-text>
				<p>Checks if input is an object:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.isObject(value): boolean</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">null</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.object.isObject(null)
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isObject({})
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{ foo: "bar" }</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isObject({ foo: "bar" })
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">["foo", "bar"]</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.isObject(["foo", "bar"])
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="$helper.object.length()" :code="false">
			<k-text>
				<p>Counts all keys in the object:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.length(object): int</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ $helper.object.length({}) }}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{ foo: "bar", baz: "qux" }</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.length({ foo: "bar", baz: "qux" })
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="$helper.object.merge()" :code="false">
			<k-text>
				<p>Merges two objects:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.merge(target, source): object</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{ foo: "bar"}, { baz: "qux" }</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.object.merge({ foo: "bar" }, { baz: "qux" })
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="$helper.object.same()" :code="false">
			<k-text>
				<p>Check if the objects are identical:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.same(a, b): boolean</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{ foo: "bar"}, { baz: "qux" }</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.object.same({ foo: "bar" }, { baz: "qux" })
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{ foo: "bar"}, { foo: "bar" }</k-code>
				</k-column>
				<k-column width="1/2">
					<k-code language="javascript">{{
						$helper.object.same({ foo: "bar" }, { foo: "bar" })
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="$helper.object.toLowerKeys()" :code="false">
			<k-text>
				<p>Converts to lowercase all keys in an object:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.object.toLowerKeys(object): object</k-code>
			</k-text>

			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-code language="javascript">{ FOO: "bar", bAz: "qux" }</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.object.toLowerKeys({ FOO: "bar", bAz: "qux" })
					}}</k-code>
				</k-column>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>
