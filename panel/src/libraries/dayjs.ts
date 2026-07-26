import dayjs from "dayjs";
import iso from "./dayjs-iso";
import parse from "./dayjs-parse";
import pattern from "./dayjs-pattern";
import round from "./dayjs-round";
import validate from "./dayjs-validate";

export type DayjsFactory = typeof dayjs;
export type DatetimeType = "date" | "time" | "datetime";

dayjs.extend(iso);
dayjs.extend(parse);
dayjs.extend(pattern);
dayjs.extend(round);
dayjs.extend(validate);

export default dayjs;
