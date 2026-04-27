import { Suspense } from "react";

import { MScrollTop } from "@banzamel/mineralui-pro/controls";
import { MLoader } from "@banzamel/mineralui-pro/feedback";
import { MSection } from "@banzamel/mineralui-pro/layout";

import { AppRoutes } from "./Routes";

export function App() {
  return (
    <Suspense
      fallback={
        <MSection spacing={"lg"}>
          <MLoader label={"Ładowanie aplikacji Vision..."} />
        </MSection>
      }
    >
      <AppRoutes />
      <MScrollTop />
    </Suspense>
  );
}
