import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import interpret from "./dayjs-interpret";
import iso from "./dayjs-iso";
import merge from "./dayjs-merge";
import pattern from "./dayjs-pattern";
import round from "./dayjs-round";
import validate from "./dayjs-validate";

dayjs.extend(customParseFormat);
dayjs.extend(interpret);
dayjs.extend(iso);
dayjs.extend(merge);
dayjs.extend(pattern);
dayjs.extend(round);
dayjs.extend(validate);

export default dayjs;
