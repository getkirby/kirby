import Vue from "vue";
import Sections from "@/components/Sections/Sections.vue";

Vue.component("k-sections", Sections);

/* Section Types */
import FieldsSection from "@/components/Sections/FieldsSection.vue";
import FilesSection from "@/components/Sections/FilesSection.vue";
import InfoSection from "@/components/Sections/InfoSection.vue";
import PagesSection from "@/components/Sections/PagesSection.vue";
import StatsSection from "@/components/Sections/StatsSection.vue";

Vue.component("k-fields-section", FieldsSection);
Vue.component("k-files-section", FilesSection);
Vue.component("k-info-section", InfoSection);
Vue.component("k-pages-section", PagesSection);
Vue.component("k-stats-section", StatsSection);
