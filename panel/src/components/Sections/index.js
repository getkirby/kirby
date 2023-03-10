import Section from "../Sections/Section.vue";
import Sections from "@/components/Sections/Sections.vue";

/* Section Types */
import FieldsSection from "@/components/Sections/FieldsSection.vue";
import FilesSection from "@/components/Sections/FilesSection.vue";
import InfoSection from "@/components/Sections/InfoSection.vue";
import PagesSection from "@/components/Sections/PagesSection.vue";
import StatsSection from "@/components/Sections/StatsSection.vue";

export default {
	install(app) {
		app.component("k-section", Section);
		app.component("k-sections", Sections);

		app.component("k-fields-section", FieldsSection);
		app.component("k-files-section", FilesSection);
		app.component("k-info-section", InfoSection);
		app.component("k-pages-section", PagesSection);
		app.component("k-stats-section", StatsSection);
	}
};
