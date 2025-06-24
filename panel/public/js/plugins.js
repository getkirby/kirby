import Vue, {
	computed,
	customRef,
	defineAsyncComponent,
	defineComponent,
	effectScope,
	getCurrentInstance,
	getCurrentScope,
	inject,
	isProxy,
	isReactive,
	isReadonly,
	isRef,
	isShallow,
	markRaw,
	nextTick,
	onActivated,
	onBeforeMount,
	onBeforeUnmount,
	onBeforeUpdate,
	onDeactivated,
	onErrorCaptured,
	onMounted,
	onRenderTracked,
	onRenderTriggered,
	onScopeDispose,
	onServerPrefetch,
	onUnmounted,
	onUpdated,
	provide,
	proxyRefs,
	readonly,
	ref,
	shallowReactive,
	shallowReadonly,
	shallowRef,
	toRaw,
	toRef,
	toRefs,
	triggerRef,
	unref,
	useAttrs,
	useCssModule,
	useCssVars,
	useListeners,
	useSlots,
	watch,
	watchEffect,
	watchPostEffect,
	watchSyncEffect
} from "vue";

// Assign Vue to the global window object
window.Vue = Vue;

// Keep named exports on the Vue constructor (like UMD build in Kirby 4)
// TODO: Remove this when Panel plugins are loaded as ES modules (Kirby 6)
Object.assign(Vue, {
	computed,
	customRef,
	defineAsyncComponent,
	defineComponent,
	effectScope,
	getCurrentInstance,
	getCurrentScope,
	inject,
	isProxy,
	isReactive,
	isReadonly,
	isRef,
	isShallow,
	markRaw,
	nextTick,
	onActivated,
	onBeforeMount,
	onBeforeUnmount,
	onBeforeUpdate,
	onDeactivated,
	onErrorCaptured,
	onMounted,
	onRenderTracked,
	onRenderTriggered,
	onScopeDispose,
	onServerPrefetch,
	onUnmounted,
	onUpdated,
	provide,
	proxyRefs,
	readonly,
	ref,
	shallowReactive,
	shallowReadonly,
	shallowRef,
	toRaw,
	toRef,
	toRefs,
	triggerRef,
	unref,
	useAttrs,
	useCssModule,
	useCssVars,
	useListeners,
	useSlots,
	watch,
	watchEffect,
	watchPostEffect,
	watchSyncEffect
});

window.panel = window.panel ?? {};
window.panel.plugins = {
	components: {},
	created: [],
	icons: {},
	routes: [],
	textareaButtons: {},
	thirdParty: {},
	use: [],
	viewButtons: {},
	views: {},
	writerMarks: {},
	writerNodes: {}
};

window.panel.plugin = function (plugin, extensions) {
	// Blocks
	resolve(extensions, "blocks", (name, options) => {
		if (typeof options === "string") {
			options = { template: options };
		}

		window.panel.plugins.components[`k-block-type-${name}`] = {
			extends: "k-block-type-default",
			...options
		};
	});

	// Components
	resolve(extensions, "components", (name, options) => {
		window.panel.plugins.components[name] = options;
	});

	// Fields
	resolve(extensions, "fields", (name, options) => {
		window.panel.plugins.components[`k-${name}-field`] = options;
	});

	// Icons
	resolve(extensions, "icons", (name, options) => {
		window.panel.plugins.icons[name] = options;
	});

	// Sections
	resolve(extensions, "sections", (name, options) => {
		window.panel.plugins.components[`k-${name}-section`] = {
			...options,
			mixins: ["section", ...(options.mixins ?? [])]
		};
	});

	// View Buttons
	resolve(extensions, "viewButtons", (name, options) => {
		window.panel.plugins.components[`k-${name}-view-button`] = options;
	});

	// `Vue.use`
	resolve(extensions, "use", (name, options) => {
		window.panel.plugins.use.push(options);
	});

	// Vue `created` callback
	if (extensions["created"]) {
		window.panel.plugins.created.push(extensions["created"]);
	}

	// Login
	if (extensions.login) {
		window.panel.plugins.login = extensions.login;
	}

	// Textarea custom toolbar buttons
	resolve(extensions, "textareaButtons", (name, options) => {
		window.panel.plugins.textareaButtons[name] = options;
	});

	// Third-party plugins
	resolve(extensions, "thirdParty", (name, options) => {
		window.panel.plugins.thirdParty[name] = options;
	});

	// Writer custom marks
	resolve(extensions, "writerMarks", (name, options) => {
		window.panel.plugins.writerMarks[name] = options;
	});

	// Writer custom nodes
	resolve(extensions, "writerNodes", function (name, options) {
		window.panel.plugins.writerNodes[name] = options;
	});
};

const resolve = (extensions, type, callback) => {
	for (const [name, options] of Object.entries(extensions[type] ?? {})) {
		callback(name, options);
	}
};
