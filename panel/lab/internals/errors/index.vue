<template>
	<k-lab-examples class="k-lab-helpers-examples">
		<k-lab-example label="Not found">
			<k-text>The backend route does not exist</k-text>
			<k-button-group>
				<k-button
					variant="filled"
					@click="$panel.view.open('/lab/does-not-exist')"
				>
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/lab/does-not-exist')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/lab/does-not-exist')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('/lab/does-not-exist')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="Backend issues">
			<k-text>The backend route throws a standard exception</k-text>

			<k-code language="php">{{
				`throw new Exception('This is a custom backend error');`
			}}</k-code>
			<br />
			<br />
			<k-button-group>
				<k-button
					variant="filled"
					@click="$panel.view.open('/lab/errors?foo=bar')"
				>
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/lab/errors?foo=bar')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/lab/errors?foo=bar')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('/lab/errors?foo=bar')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="Backend issues">
			<k-text>
				The backend route throws a Kirby exception with details (we use this for
				form errors for example)
			</k-text>

			<k-code language="php">{{
				`throw new Kirby\\Exception\\InvalidArgumentException(
	fallback: 'Exception with details',
	details: [
		'a' => [
			'label'   => 'Detail A',
			'message' => [
				'This is a message for Detail A',
			],
		],
		'b' => [
			'label'   => 'Detail B',
			'message' => [
				'This is the first message for Detail B',
				'This is the second message for Detail B',
			],
		],
	]
);`
			}}</k-code>
			<br />
			<br />
			<k-button-group>
				<k-button
					variant="filled"
					@click="$panel.view.open('/lab/errors/form')"
				>
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/lab/errors/form')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/lab/errors/form')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('/lab/errors/form')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="JSON parsing issue">
			<k-text>
				The server returns a JSON response, but the JSON is not parseable
			</k-text>
			<k-button-group>
				<k-button
					variant="filled"
					@click="$panel.view.open('/lab/errors/invalid-json')"
				>
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/lab/errors/invalid-json')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/lab/errors/invalid-json')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('/lab/errors/invalid-json')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="HTML response">
			<k-text>
				The server responds with HTML instead of JSON. The request handler
				should redirect to the route instead of trying to evaluate it.
			</k-text>
			<k-button-group>
				<k-button
					variant="filled"
					@click="$panel.view.open('/lab/errors/html')"
				>
					View
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.dialog.open('/lab/errors/html')"
				>
					Dialog
				</k-button>
				<k-button
					variant="filled"
					@click="$panel.drawer.open('/lab/errors/html')"
				>
					Drawer
				</k-button>
				<k-button variant="filled" @click="request('/lab/errors/html')">
					Request
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="JS issues">
			<k-button-group>
				<k-button variant="filled" @click="throwError()">
					Throw error (console)
				</k-button>
				<k-button variant="filled" @click="throwAndCatchError()">
					Throw & catch error
				</k-button>
			</k-button-group>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		async request(path) {
			try {
				await this.$panel.get("/requests" + path);
			} catch (e) {
				this.$panel.error(e);
			}
		},
		throwAndCatchError() {
			try {
				throw new Error("This is a custom error");
			} catch (e) {
				this.$panel.error(e);
			}
		},
		throwError() {
			throw new Error("This is a custom error");
		}
	}
};
</script>
