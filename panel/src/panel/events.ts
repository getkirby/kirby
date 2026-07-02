import clipboard from "@/helpers/clipboard";
import { lcfirst } from "@/helpers/string";
import mitt from "mitt";

/**
 * Global event delegation and event bus
 * which can be used by any component in the app
 * to start and stop listening to events
 * @since 4.0.0
 */
export default function (panel: TODO) {
	const emitter = mitt();

	/**
	 * Custom handler for online/offline state
	 */
	emitter.on("online", () => {
		panel.isOffline = false;
	});
	emitter.on("offline", () => {
		panel.isOffline = true;
	});

	/**
	 * Custom handler for save events
	 */
	emitter.on("keydown.cmd.s", (e) => {
		emitter.emit(panel.context + ".save", e);
	});

	/**
	 * Custom handler for search
	 */
	emitter.on("keydown.cmd.shift.f", () => panel.search());
	emitter.on("keydown.cmd./", () => panel.search());

	/**
	 * Custom copy to clipboard event
	 * @since 5.0.0
	 */
	emitter.on("clipboard.write", async (e) => {
		clipboard.write(e);
		panel.notification.success(panel.t("copy.success") + "!");
	});

	/**
	 * Config for globally delegated events.
	 * Some events need to be fired on the document
	 * and some on window. The boolean value determines if
	 * they capture events on children or not.
	 */
	const events: {
		document: Record<string, boolean>;
		window: Record<string, boolean>;
	} = {
		document: {
			blur: true,
			click: false,
			copy: true,
			focus: true,
			paste: true
		},
		window: {
			beforeunload: false,
			dragenter: false,
			dragexit: false,
			dragleave: false,
			dragover: false,
			drop: false,
			keydown: false,
			keyup: false,
			offline: false,
			online: false,
			popstate: false
		}
	};

	/**
	 * Each globally delegated event
	 * has its own method. This helps
	 * to add and remove the event listeners
	 * properly and some event listeners also
	 * have a bit of extra treatment in those
	 * methods (i.e. drag events)
	 */
	return {
		/**
		 * Global window beforeunload event
		 */
		beforeunload(e: BeforeUnloadEvent): void {
			this.emit("beforeunload", e);
		},

		/**
		 * Global blur event
		 */
		blur(e: FocusEvent): void {
			this.emit("blur", e);
		},

		/**
		 * Global click event
		 */
		click(e: MouseEvent): void {
			this.emit("click", e);
		},

		/**
		 * Global clipboard copy event
		 */
		copy(e: ClipboardEvent): void {
			this.emit("copy", e);
		},

		/**
		 * Global dragenter event, which
		 * prevents the default and keeps
		 * track of the entered element.
		 */
		dragenter(e: DragEvent): void {
			this.entered = e.target;
			this.prevent(e);
			this.emit("dragenter", e);
		},

		/**
		 * Global dragexit event, which
		 * prevents the default
		 */
		dragexit(e: DragEvent): void {
			this.prevent(e);
			this.entered = null;
			this.emit("dragexit", e);
		},

		/**
		 * Global dragleave event, which
		 * prevents the default and also
		 * is only fired when the entered
		 * element matches with the left element
		 */
		dragleave(e: DragEvent): void {
			this.prevent(e);

			if (this.entered === e.target) {
				this.entered = null;
				this.emit("dragleave", e);
			}
		},

		/**
		 * Global dragover event, which
		 * prevents the default
		 */
		dragover(e: DragEvent): void {
			this.prevent(e);
			this.emit("dragover", e);
		},

		/**
		 * Global drop event, which
		 * prevents the default and
		 * enables dropping elements
		 * on any Panel component
		 */
		drop(e: DragEvent): void {
			this.prevent(e);
			this.entered = null;
			this.emit("drop", e);
		},

		/**
		 * Proxy for mitt's emit method
		 */
		emit: emitter.emit as (type: string, event?: unknown) => void,

		/**
		 * Keeps track of the entered element
		 * on drag events
		 */
		entered: null as EventTarget | null,

		/**
		 * Global focus event
		 */
		focus(e: FocusEvent): void {
			this.emit("focus", e);
		},

		/**
		 * The keychain helper function creates
		 * a key modifier string which is used in
		 * global keyup and keydown events to send a
		 * more specific global event. This is super
		 * useful if you only want to listen to a
		 * particular keyboard shortcut.
		 *
		 * @example
		 * window.panel.events.on("keydown.esc", () => {})
		 * window.panel.events.on("keydown.cmd.s", () => {})
		 */
		keychain(type: string, event: KeyboardEvent): string {
			const parts = [type];

			// with meta or control key
			if (event.metaKey || event.ctrlKey) {
				parts.push("cmd");
			}

			if (event.altKey === true) {
				parts.push("alt");
			}

			if (event.shiftKey === true) {
				parts.push("shift");
			}

			let key = event.key ? lcfirst(event.key) : null;

			// key replacements
			const keys: Record<string, string> = {
				escape: "esc",
				arrowUp: "up",
				arrowDown: "down",
				arrowLeft: "left",
				arrowRight: "right"
			};

			if (key && keys[key]) {
				key = keys[key];
			}

			if (key && ["alt", "control", "shift", "meta"].includes(key) === false) {
				parts.push(key);
			}

			return parts.join(".");
		},

		/**
		 * Global keydown event which also
		 * fires a more useful event with
		 * key modifiers. I.e. keydown.esc
		 */
		keydown(e: KeyboardEvent): void {
			this.emit(this.keychain("keydown", e), e);
			this.emit("keydown", e);
		},

		/**
		 * Global keyup event which also
		 * fires a more useful event with
		 * key modifiers. I.e. keyup.esc
		 */
		keyup(e: KeyboardEvent): void {
			this.emit(this.keychain("keyup", e), e);
			this.emit("keyup", e);
		},

		/**
		 * Proxy for mitt's off method
		 */
		off: emitter.off as (
			type: string,
			handler?: (event?: unknown) => void
		) => void,

		/**
		 * The Panel just went offline
		 */
		offline(e: Event): void {
			this.emit("offline", e);
		},

		/**
		 * Proxy for mitt's on method
		 */
		on: emitter.on as (
			type: string,
			handler?: (event?: unknown) => void
		) => void,

		/**
		 * The Panel is online again after
		 * being offline
		 */
		online(e: Event): void {
			this.emit("online", e);
		},

		/**
		 * Global clipboard paste event
		 */
		paste(e: ClipboardEvent): void {
			this.emit("paste", e);
		},

		/**
		 * Browser back button event
		 */
		popstate(e: PopStateEvent): void {
			this.emit("popstate", e);
		},

		/**
		 * Prevents the event from bubbling
		 * and stops the default behavior.
		 */
		prevent(e: Event): void {
			e.stopPropagation();
			e.preventDefault();
		},

		/**
		 * Registers all global event listeners
		 * from the events config. This is used
		 * in the created hook of the app.
		 */
		subscribe(): void {
			const self = this as unknown as Record<string, EventListener>;

			for (const event in events.document) {
				document.addEventListener(
					event,
					self[event].bind(this),
					events.document[event]
				);
			}

			for (const event in events.window) {
				window.addEventListener(
					event,
					self[event].bind(this),
					events.window[event]
				);
			}
		},

		/**
		 * Removes all global event listeners
		 * from the events config. This is
		 * used in the destroyed hook of the app
		 */
		unsubscribe(): void {
			const self = this as unknown as Record<string, EventListener>;

			for (const event in events.document) {
				document.removeEventListener(event, self[event]);
			}

			for (const event in events.window) {
				window.removeEventListener(event, self[event]);
			}
		}
	};
}
