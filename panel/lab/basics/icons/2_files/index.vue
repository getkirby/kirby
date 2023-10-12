<template>
	<k-lab-examples>
		<k-lab-example label="Files" :code="false">
			<div class="k-table">
				<table>
					<thead>
						<tr>
							<th class="k-table-cell" style="width: var(--table-row-height)">
								<k-icon-frame icon="image" />
							</th>
							<th>Type</th>
							<th>Icon</th>
							<th>Color</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(preview, index) in previews" :key="index">
							<td class="k-table-cell">
								<k-icon-frame
									:icon="getIcon(preview)"
									:color="getColor(preview)"
									back="pattern"
								/>
							</td>
							<td>{{ getType(preview) }}</td>
							<td>{{ getIcon(preview) }}</td>
							<td class="k-text">
								<code>--color-{{ getColor(preview) }}</code>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		colors() {
			return {
				types: {
					archive: "gray-500",
					audio: "aqua-500",
					code: "pink-500",
					document: "red-500",
					image: "orange-500",
					video: "yellow-500"
				},
				extensions: {
					csv: "green-500",
					doc: "blue-500",
					docx: "blue-500",
					indd: "purple-500",
					rtf: "blue-500",
					xls: "green-500",
					xlsx: "green-500"
				}
			};
		},
		icons() {
			return {
				types: {
					archive: "archive",
					audio: "audio",
					code: "code",
					document: "document",
					image: "image",
					video: "video"
				},
				extensions: {
					csv: "table",
					doc: "pen",
					docx: "pen",
					md: "markdown",
					mdown: "markdown",
					rtf: "pen",
					xls: "table",
					xlsx: "table"
				}
			};
		},
		previews() {
			return [
				{ type: "archive" },
				{ type: "audio" },
				{ type: "code" },
				{ type: "document" },
				{ type: "image" },
				{ type: "video" },
				{ extension: "csv", type: "document" },
				{ extension: "doc", type: "document" },
				{ extension: "docx", type: "document" },
				{ extension: "indd", type: "document" },
				{ extension: "mdown", type: "document" },
				{ extension: "md", type: "document" },
				{ extension: "rtf", type: "document" },
				{ extension: "xls", type: "document" },
				{ extension: "xlsx", type: "document" }
			];
		}
	},
	methods: {
		getColor(preview) {
			return (
				this.colors.extensions[preview.extension] ??
				this.colors.types[preview.type]
			);
		},
		getIcon(preview) {
			return (
				this.icons.extensions[preview.extension] ??
				this.icons.types[preview.type]
			);
		},
		getType(preview) {
			if (preview.extension) {
				return "ext:" + preview.extension;
			}

			return "type:" + preview.type;
		}
	}
};
</script>
