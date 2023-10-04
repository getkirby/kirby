<template>
	<k-ui-examples>
		<k-grid
			style="
				--columns: 2;
				gap: var(--spacing-12);
				margin-bottom: var(--spacing-12);
			"
		>
			<div>
				<k-ui-example label="select">
					<select>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="select disabled">
					<select disabled>
						<option>Select A</option>
						<option>Select B</option>
					</select>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=text]">
					<input type="text" />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=text] disabled">
					<input type="text" disabled />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=checkbox]">
					<input type="checkbox" />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=checkbox] disabled">
					<input type="checkbox" disabled />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=radio]">
					<input type="radio" />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=radio] disabled">
					<input type="radio" disabled />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="textarea">
					<textarea>Textarea</textarea>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="textarea" disabled>
					<textarea disabled>Textarea</textarea>
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=range]">
					<input type="range" />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="input[type=range] disabled">
					<input type="range" disabled />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="buttons">
					<button>Button</button>
					<input type="submit" value="Submit" />
					<input type="reset" value="Reset" />
				</k-ui-example>
			</div>
			<div>
				<k-ui-example label="buttons disabled">
					<button disabled>Button</button>
					<input disabled type="submit" value="Submit" />
					<input disabled type="reset" value="Reset" />
				</k-ui-example>
			</div>
		</k-grid>
		<k-ui-example label="label">
			<div>
				<label for="input">Connected</label>
				<input type="text" id="input" />
			</div>
			<div>
				<label>Not connected</label>
			</div>
		</k-ui-example>
		<k-ui-example label="fieldset">
			<fieldset>
				<legend>Fieldset Legend</legend>
			</fieldset>
		</k-ui-example>
	</k-ui-examples>
</template>
