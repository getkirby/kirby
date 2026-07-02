<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-box theme="text">
			<k-text>
				Access the following string helpers in your Vue components through
				<code>this.$helper.string</code>
			</k-text>
		</k-box>

		<k-lab-example label="$helper.string.camelToKebab()" script="camelCase">
			<k-text>
				<p>Converts camel-case to kebab-case:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.camelToKebab(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="camelCase" @input="camelCase = $event" />
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

		<k-lab-example label="$helper.string.escapeHTML()" script="html">
			<k-text>
				<p>Escapes HTML in string:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.escapeHTML(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="html" @input="html = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{ $helper.string.escapeHTML(html) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.hasEmoji()" script="emoji">
			<k-text>
				<p>Checks if string contains an emoji:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.hasEmoji(string): boolean</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="emoji" @input="emoji = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.string.hasEmoji(emoji)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.isEmpty()" script="empty">
			<k-text>
				<p>Checks if a string is empty:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.isEmpty(string): boolean</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="empty" @input="empty = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="javascript">{{
						$helper.string.isEmpty(empty)
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.lcfirst()" script="lcfirst">
			<k-text>
				<p>Turns first letter lowercase:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.lcfirst(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="lcfirst" @input="lcfirst = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.lcfirst(lcfirst) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.ltrim()" script="ltrim">
			<k-text>
				<p>Trims the given character(s) at the beginning of the string:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.ltrim(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="ltrim" @input="ltrim = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.ltrim(ltrim, "*")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.pad()" script="pad">
			<k-text>
				<p>Prefixes string with 0 until length is reached:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.pad(string, length): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/3">
					<h2>Input</h2>
					<k-input type="text" :value="padString" @input="padString = $event" />
				</k-column>
				<k-column width="1/3">
					<h2>Length</h2>
					<k-input type="number" :value="padCount" @input="padCount = $event" />
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

		<k-lab-example label="$helper.string.random()" script="random">
			<k-text>
				<p>Generate random alpha-num string of specified length:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.random(length): string</k-code>
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

		<k-lab-example label="$helper.string.rtrim()" script="rtrim">
			<k-text>
				<p>Trims the given character(s) at the end of the string:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.rtrim(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="rtrim" @input="rtrim = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.rtrim(rtrim, "*")
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.sanitizeHTML()" script="html">
			<k-text>
				<p>Sanitizes HTML:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.sanitizeHTML(html): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="html" @input="html = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{ $helper.string.sanitizeHTML(html) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>


		<k-lab-example label="$helper.string.slug()" script="slug">
			<k-text>
				<p>Convert string to ASCII slug:</p>

				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.slug(string, rules, allowed, separator): string</k-code>

				<ul>
					<li>
						<code>rules</code> (array): Array of custom rules for replacing
						characters, e.g. <code>[{ "ä": "ae" }]</code>
					</li>
					<li>
						<code>allowed</code> (string): Allowed characters, default is
						<code>a-z0-9</code>
					</li>
					<li>
						<code>separator</code> (string): Separator for the slug, default is
						<code>-</code>
					</li>
				</ul>
			</k-text>

			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="slug" @input="slug = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{
						$helper.string.slug(slug, [{ ä: "ae" }])
					}}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.stripHTML()" script="html">
			<k-text>
				<p>Removes HTML from a string:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.stripHTML(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="html" @input="html = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="html">{{ $helper.string.stripHTML(html) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.ucfirst()" script="ucfirst">
			<k-text>
				<p>Turns first letter uppercase:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.ucfirst(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="ucfirst" @input="ucfirst = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.ucfirst(ucfirst) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.template()" script="template">
			<k-text>
				<p>Replaces template placeholders in string with provided values:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.template(string, values): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="template" @input="template = $event" />
					<br />
					<k-code language="javascript">{{ templateValues }}</k-code>
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

		<k-lab-example label="$helper.string.ucwords()" script="ucwords">
			<k-text>
				<p>Turns first letter of each word uppercase:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.ucwords(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="ucwords" @input="ucwords = $event" />
				</k-column>
				<k-column width="1/2">
					<h2>Result</h2>
					<k-code language="text">{{ $helper.string.ucwords(ucwords) }}</k-code>
				</k-column>
			</k-grid>
			<!-- @code-end -->
		</k-lab-example>

		<k-lab-example label="$helper.string.unescapeHTML()" script="unhtml">
			<k-text>
				<p>Turns escaped HTML entities into actual characters again:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.unescapeHTML(string): string</k-code>
			</k-text>
			<!-- @code -->
			<k-grid variant="fields">
				<k-column width="1/2">
					<h2>Input</h2>
					<k-input type="text" :value="unhtml" @input="unhtml = $event" />
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

		<k-lab-example label="$helper.string.uuid()">
			<k-text>
				<p>Returns a unique ID:</p>
				<!-- prettier-ignore -->
				<k-code language="javascript">this.$helper.string.uuid(): string</k-code>
			</k-text>
			<!-- @code -->
			<h2>Result</h2>
			<k-code language="javascript">{{ $helper.string.uuid() }}</k-code>
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
		};
	}
};
/** @script-end */

/** @script: html */
export const html = {
	data() {
		return {
			html: "This is <div>HTML</div>"
		};
	}
};
/** @script-end */

/** @script: emoji */
export const emoji = {
	data() {
		return {
			emoji: "This string contains an emoji 🎉"
		};
	}
};
/** @script-end */

/** @script: empty */
export const empty = {
	data() {
		return {
			empty: " "
		};
	}
};
/** @script-end */

/** @script: lcfirst */
export const lcfirst = {
	data() {
		return {
			lcfirst: "This should start with a lowercase letter"
		};
	}
};
/** @script-end */

/** @script: ltrim */
export const ltrim = {
	data() {
		return {
			ltrim: "***This had *'s at the beginning"
		};
	}
};
/** @script-end */

/** @script: pad */
export const pad = {
	data() {
		return {
			padString: "99",
			padCount: 5
		};
	}
};
/** @script-end */

/** @script: random */
export const random = {
	data() {
		return {
			random: this.$helper.string.random(20)
		};
	}
};
/** @script-end */

/** @script: rtrim */
export const rtrim = {
	data() {
		return {
			rtrim: "This had *'s at the end***"
		};
	}
};
/** @script-end */

/** @script: slug */
export const slug = {
	data() {
		return {
			slug: "This is a string with späces"
		};
	}
};
/** @script-end */

/** @script: template */
export const template = {
	data() {
		return {
			template: "This is { foo } and {{ baz }}"
		};
	},
	computed: {
		templateValues() {
			return {
				foo: "bar",
				baz: "qux"
			};
		}
	}
};
/** @script-end */

/** @script: ucfirst */
export const ucfirst = {
	data() {
		return {
			ucfirst: "this should start with an uppercase letter"
		};
	}
};
/** @script-end */

/** @script: ucwords */
export const ucwords = {
	data() {
		return {
			ucwords: "this is a string with multiple words"
		};
	}
};
/** @script-end */

/** @script: unhtml */
export const unhtml = {
	data() {
		return {
			unhtml: "This is &lt;div&gt;HTML&lt;/div&gt;"
		};
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
