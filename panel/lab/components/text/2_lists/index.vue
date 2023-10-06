<template>
	<k-lab-examples>
		<k-lab-example label="ul">
			<k-text>
				<ul>
					<li>List Item A</li>
					<li>List Item B</li>
					<li>List Item C</li>
				</ul>
				<ul>
					<li>
						List Item A
						<ul>
							<li>
								List Item AA
								<ul>
									<li>
										List Item AAA
										<ul>
											<li>List Item AAAA</li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>
					</li>
				</ul>
			</k-text>
		</k-lab-example>
		<k-lab-example label="ol">
			<k-text>
				<ol>
					<li>List Item A</li>
					<li>List Item B</li>
					<li>List Item C</li>
				</ol>
				<ol>
					<li>
						List Item A
						<ol>
							<li>
								List Item AA
								<ol>
									<li>
										List Item AAA
										<ol>
											<li>List Item AAAA</li>
										</ol>
									</li>
								</ol>
							</li>
						</ol>
					</li>
				</ol>
			</k-text>
		</k-lab-example>
		<k-lab-example label="mixed">
			<k-text>
				<ol>
					<li>
						List Item A
						<ul>
							<li>
								List Item AA
								<ol>
									<li>
										List Item AAA
										<ul>
											<li>List Item AAAA</li>
										</ul>
									</li>
								</ol>
							</li>
						</ul>
					</li>
				</ol>
				<ul>
					<li>
						List Item A
						<ol>
							<li>
								List Item AA
								<ul>
									<li>
										List Item AAA
										<ol>
											<li>List Item AAAA</li>
										</ol>
									</li>
								</ul>
							</li>
						</ol>
					</li>
				</ul>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
