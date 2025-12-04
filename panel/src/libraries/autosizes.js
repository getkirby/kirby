(function () {
	// Check if the browser already supports sizes="auto"
	// We're using UA sniffing here, as there's no way to detect browser
	// support without a forced layout, which would make things slower for everyone.
	const polyfillAutoSizes = () => {
		// Avoid polyfilling if the browser is too old and doesn't support performance observer and paint timing
		if (
			!("PerformanceObserver" in window) ||
			!PerformanceObserver.supportedEntryTypes.includes("paint")
		) {
			return false;
		}

		const userAgent = navigator.userAgent;
		const chromeMatch = userAgent.match(/Chrome\/(\d+)/);

		if (!chromeMatch) {
			return true;
		}

		const chromeVersion = parseInt(chromeMatch[1], 10);
		return chromeVersion < 126;
	};

	if (!polyfillAutoSizes()) {
		return;
	}

	const attributes = ["src", "srcset"];
	const prefix = "data-auto-sizes-";
	let didFirstContentfulPaintRun = false;
	function getSizesValueFromElement(elem) {
		const width = elem ? Math.round(elem.getBoundingClientRect().width) : 0;
		if (width <= 0) {
			return null;
		}
		// Set the sizes attribute to the computed width in pixels
		return `${width}px`;
	}
	function calculateAndSetSizes(img) {
		// Calculate the displayed width of the image
		// getBoundingClientRect() forces layout, but this is running right after FCP,
		// so hopefully the DOM is not dirty.
		let sizes = getSizesValueFromElement(img);
		if (!sizes) {
			// If we get no width for the image, use the parent's width
			sizes = getSizesValueFromElement(img.parentElement);
		}
		if (sizes) {
			img.sizes = sizes;
		}
	}

	// Store the original src and srcset attributes, and remove them to prevent loading before
	// we've calculated the sizes attribute
	function preventNonAutoImageLoad(images) {
		for (const img of images) {
			if (img.complete) {
				// Don't do any of this if the image is already loaded
				continue;
			}
			// Only process images with sizes attribute starting with "auto" and loading="lazy"
			if (
				!(img.getAttribute("sizes") || "").trim().startsWith("auto") ||
				img.getAttribute("loading") !== "lazy"
			) {
				continue;
			}
			if (!didFirstContentfulPaintRun) {
				for (const attribute of attributes) {
					if (img.hasAttribute(attribute)) {
						// Store original src and srcset
						img.setAttribute(
							`${prefix}${attribute}`,
							img.getAttribute(attribute)
						);
						// Remove src and srcset to prevent loading
						img.removeAttribute(attribute);
					}
				}
			} else {
				// Calculate sizes without removing src and srcset
				calculateAndSetSizes(img);
			}
		}
	}

	// Set the sizes attribute to the computed width in pixels and restore original src and srcset
	function restoreImageAttributes() {
		const images = document.querySelectorAll(
			`img[${prefix}src], img[${prefix}srcset]`
		);

		for (const img of images) {
			calculateAndSetSizes(img);

			for (const attribute of attributes) {
				const tempAttribute = `${prefix}${attribute}`;
				if (img.hasAttribute(tempAttribute)) {
					img[attribute] = img.getAttribute(tempAttribute);
					img.removeAttribute(tempAttribute);
				}
			}
		}
	}

	// Set up mutation observer to detect new images
	const observer = new MutationObserver((mutations) => {
		const newImages = [];

		for (const mutation of mutations) {
			if (mutation.type === "childList") {
				for (const node of mutation.addedNodes) {
					if (node.nodeName === "IMG") {
						newImages.push(node);
					}
					// Add all images within added nodes
					if (node.querySelectorAll) {
						newImages.push(...node.querySelectorAll("img"));
					}
				}
			}
			// Check for attribute changes on images
			else if (
				mutation.type === "attributes" &&
				mutation.target.nodeName === "IMG" &&
				(mutation.attributeName === "sizes" ||
					mutation.attributeName === "loading" ||
					mutation.attributeName === "src" ||
					mutation.attributeName === "srcset")
			) {
				newImages.push(mutation.target);
			}
		}

		if (newImages.length > 0) {
			preventNonAutoImageLoad(newImages);
		}
	});

	// Start observing the document
	observer.observe(document.documentElement, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ["sizes", "loading"]
	});

	// Prevent the load of any existing images when the polyfill loads.
	preventNonAutoImageLoad(
		document.querySelectorAll('img[sizes^="auto"][loading="lazy"]')
	);

	new PerformanceObserver((entries, observer) => {
		entries.getEntriesByName("first-contentful-paint").forEach(() => {
			didFirstContentfulPaintRun = true;
			setTimeout(restoreImageAttributes, 0);
			observer.disconnect();
		});
	}).observe({ type: "paint", buffered: true });
})();
