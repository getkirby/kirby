<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-text>
			Access the following string helpers in your Vue components through
			<code>this.$helpers.string</code>
		</k-text>

		<k-lab-example label="$helpers.string.camelToKebab()" script="camelCase">
			<k-text>
				<p>
					Converts camel-case to kebab-case:
					<code>this.$helpers.string.camelToKebab(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="camelCase"
						@input="camelCase = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.camelToKebab(camelCase)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.escapeHTML()" script="html">
			<k-text>
				<p>
					Escapes HTML in string:
					<code>this.$helpers.string.escapeHTML(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="html"
						@input="html = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{
						$helper.string.escapeHTML(html)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.hasEmoji()" script="emoji">
			<k-text>
				<p>
					Checks if string contains an emoji:
					<code>this.$helpers.string.hasEmoji(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="emoji"
						@input="emoji = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ $helper.string.hasEmoji(emoji) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.isEmpty()" script="empty">
			<k-text>
				<p>
					Checks if a string is empty:
					<code>this.$helpers.string.isEmpty(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="empty"
						@input="empty = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{ $helper.string.isEmpty(empty) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.lcfirst()" script="lcfirst">
			<k-text>
				<p>
					Turns first letter lowercase:
					<code>this.$helpers.string.lcfirst(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="lcfirst"
						@input="lcfirst = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.lcfirst(lcfirst) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.ltrim()" script="ltrim">
			<k-text>
				<p>
					Trims the given character(s) at the beginning of the string:
					<code>this.$helpers.string.ltrim(string, replace)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="ltrim"
						@input="ltrim = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.ltrim(ltrim, '*') }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.pad()" script="pad">
			<k-text>
				<p>
					Prefixes string with 0 until length is reached:
					<code>this.$helpers.string.pad(string, length)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/3">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="padString"
						@input="padString = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<h2>How many digits?</h2>
					<k-input
						type="number"
						:value="padCount"
						@input="padCount = $event"
					/>
				</k-column>
				<k-column width="1/3">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.string.pad(padString, padCount)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.random()" script="random">
			<k-text>
				<p>
					Generate random alpha-num string of specified length:
					<code>this.$helpers.string.random(length)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Length</h2>
					<k-input
						type="number"
						value="20"
						@input="random = $helper.string.random($event)"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ random }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.rtrim()" script="rtrim">
			<k-text>
				<p>
					Trims the given character(s) at the end of the string:
					<code>this.$helpers.string.rtrim(string, replace)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="rtrim"
						@input="rtrim = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.rtrim(rtrim, '*') }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.slug()" script="slug">
			<k-text>
				<p>
					Convert string to ASCII slug:
					<code>this.$helpers.string.slug(string, rules, allowed, separator)</code>
				</p>

				<ul>
					<li>
						<code>rules</code> (array): Array of custom rules for replacing characters, e.g. <code>[{ "ä": "ae" }]</code>
					</li>
					<li>
						<code>allowed</code> (string): Allowed characters, default is <code>a-z0-9</code>
					</li>
					<li>
						<code>separator</code> (string): Separator for the slug, default is <code>-</code>
					</li>
				</ul>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="slug"
						@input="slug = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.slug(slug, [{'ä': 'ae' }])
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.stripHTML()" script="html">
			<k-text>
				<p>
					Removes HTML from a string:
					<code>this.$helpers.string.stripHTML(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="html"
						@input="html = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{
						$helper.string.stripHTML(html)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.ucfirst()" script="ucfirst">
			<k-text>
				<p>
					Turns first letter uppercase:
					<code>this.$helpers.string.ucfirst(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="ucfirst"
						@input="ucfirst = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.ucfirst(ucfirst)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.template()" script="template">
			<k-text>
				<p>
					Replaces template placeholders in string with provided values:
					<code>this.$helpers.string.template(string, values)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="template"
						@input="template = $event"
					/>
					<br>
					<k-code language="javascript">{{
						templateValues
					}}</k-code>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.template(template, templateValues)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.ucwords()" script="ucwords">
			<k-text>
				<p>
					Turns first letter of each word uppercase:
					<code>this.$helpers.string.ucwords(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="ucwords"
						@input="ucwords = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.ucwords(ucwords)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.unescapeHTML()" script="unhtml">
			<k-text>
				<p>
					Turns escaped HTML entities into actual characters again:
					<code>this.$helpers.string.unescapeHTML(string)</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input
						type="text"
						:value="unhtml"
						@input="unhtml = $event"
					/>
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{
						$helper.string.unescapeHTML(unhtml)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helpers.string.uuid()">
			<k-text>
				<p>
					Returns a unique ID:
					<code>this.$helpers.string.uuid()</code>
				</p>
			</k-text>
			<!-- @code -->
			<k-code language="javascript">{{
				$helper.string.uuid()
			}}</k-code>
			<!-- @code-end -->
		</k-lab-example>

	</k-lab-examples>
</template>

<script>
/** @script: camelCase */
export const camelCase = {
	data() {
		return {
			camelCase: "myCamelCase"
		}
	}
};
/** @script-end */

/** @script: html */
export const html = {
	data() {
		return {
			html: "This is <div>HTML</div>",
		}
	}
};
/** @script-end */

/** @script: emoji */
export const emoji = {
	data() {
		return {
			emoji: "This string contains an emoji 🎉",
		}
	}
};
/** @script-end */

/** @script: empty */
export const empty = {
	data() {
		return {
			empty: " ",
		}
	}
};
/** @script-end */

/** @script: lcfirst */
export const lcfirst = {
	data() {
		return {
			lcfirst: "This should start with a lowercase letter",
		}
	}
};
/** @script-end */

/** @script: ltrim */
export const ltrim = {
	data() {
		return {
			ltrim: "***This had *'s at the beginning",
		}
	}
};
/** @script-end */

/** @script: pad */
export const pad = {
	data() {
		return {
			padString: "99",
			padCount: 5,
		}
	}
};
/** @script-end */

/** @script: random */
export const random = {
	data() {
		return {
			random: this.$helper.string.random(20)
		}
	}
};
/** @script-end */

/** @script: rtrim */
export const rtrim = {
	data() {
		return {
			rtrim: "This had *'s at the end***",
		}
	}
};
/** @script-end */

/** @script: slug */
export const slug = {
	data() {
		return {
			slug: "This is a string with späces",
		}
	}
};
/** @script-end */

/** @script: template */
export const template = {
	data() {
		return {
			template: "This is { foo } and {{ baz }}",
		}
	},
	computed: {
		templateValues() {
			return {
				"foo": "bar",
				"baz": "qux"
			}
		}

	}
};
/** @script-end */

/** @script: ucfirst */
export const ucfirst = {
	data() {
		return {
			ucfirst: "this should start with an uppercase letter",
		}
	}
};
/** @script-end */

/** @script: ucwords */
export const ucwords = {
	data() {
		return {
			ucwords: "this is a string with multiple words",
		}
	}
};
/** @script-end */

/** @script: unhtml */
export const unhtml = {
	data() {
		return {
			unhtml: "This is &lt;div&gt;HTML&lt;/div&gt;"
		}
	}
};
/** @script-end */

export default {
	mixins: [
		camelCase,
		html,
		emoji,
		empty,
		lcfirst,
		ltrim,
		pad,
		random,
		rtrim,
		slug,
		template,
		ucfirst,
		ucwords,
		unhtml
	]
};
</script>
