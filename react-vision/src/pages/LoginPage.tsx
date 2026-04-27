import {useState} from "react";
import {Link, useNavigate} from "react-router-dom";

import {
    MCard,
    MCardBody,
    MCardFooter,
    MCardHeader,
} from "@banzamel/mineralui-pro/cards";
import {MButton} from "@banzamel/mineralui-pro/controls";
import {MReveal} from "@banzamel/mineralui-pro/display";
import {MBadge, useMToast} from "@banzamel/mineralui-pro/feedback";
import {useMI18n} from "@banzamel/mineralui-pro/i18n";
import {MDashboardIllustration} from "@banzamel/mineralui-pro/illustrations";
import {MInputEmail, MInputPassword} from "@banzamel/mineralui-pro/inputs";
import {
    MContainer,
    MGrid,
    MGridItem,
    MInline,
    MSection,
    MStack,
} from "@banzamel/mineralui-pro/layout";
import {MImage} from "@banzamel/mineralui-pro/media";
import {MHeading, MLink, MText} from "@banzamel/mineralui-pro/typography";

import {PwaInstallButton} from "../components/PwaInstallButton";
import {useAuth} from "../features/auth/AuthContext";
import {useInstallStatus} from "../features/installer";
import {useErrorToast} from "../helpers";
import pl from "../i18n/pl.json";
import packageJson from "../../package.json";

export function LoginPage() {
    const navigate = useNavigate();
    const {toast} = useMToast();
    const {t} = useMI18n<typeof pl>();
    const {login} = useAuth();
    const {showError} = useErrorToast();
    const {installed} = useInstallStatus();
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [submitting, setSubmitting] = useState(false);

    async function handleLogin() {
        if (submitting) return;
        if (!email.trim() || !password.trim()) {
            toast({
                title: t("login.error_title"),
                message: t("login.error_message"),
                color: "error",
            });
            return;
        }

        setSubmitting(true);
        try {
            await login({email, password});
            navigate("/");
        } catch (err) {
            showError(err, {
                title: t("login.error_title"),
                fallback: t("login.error_message"),
            });
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <MSection
            as={"main"}
            style={{minHeight: "100dvh", display: "flex", alignItems: "center"}}
        >
            <MContainer size={"wide"}>
                <MGrid type={"row"} padding={"lg"}>
                    <MGridItem sm={12} lg={7}>
                        <MReveal direction={"right"} distance={24} trigger={"mount"}>
                            <MCard>
                                <MCardHeader>
                                    <MStack spacing={"xs"}>
                                        <MHeading level={2} tone={"accent"}>
                                            {t("login.title")}
                                        </MHeading>
                                        <MText tone={"muted"}>{t("login.subtitle")}</MText>
                                    </MStack>
                                </MCardHeader>
                                <MCardBody>
                                    <MStack spacing={"md"}>
                                        <MInputEmail
                                            label={t("login.email")}
                                            value={email}
                                            autoComplete={"username"}
                                            onChange={(event) => setEmail(event.target.value)}
                                            fullWidth
                                        />
                                        <MInputPassword
                                            label={t("login.password")}
                                            value={password}
                                            autoComplete={"current-password"}
                                            onChange={(event) => setPassword(event.target.value)}
                                            fullWidth
                                        />
                                        <MInline justify={"end"} style={{paddingTop: "12px"}}>
                                            <MButton
                                                variant={"filled"}
                                                color={"primary"}
                                                loading={submitting}
                                                onClick={handleLogin}
                                            >
                                                {t("login.submit")}
                                            </MButton>
                                        </MInline>
                                    </MStack>
                                </MCardBody>
                                <MCardFooter>
                                    <MInline wrap={"wrap"} justify={"between"}>
                                        <MInline wrap={"wrap"}>
                                            <MBadge color={"info"}>v{packageJson.version}</MBadge>
                                            {!installed && (
                                                <MText size={"sm"}>
                                                    <MLink component={Link} to={"/install"} tone={"accent"}>
                                                        {t("navbar.install_link")}
                                                    </MLink>
                                                </MText>
                                            )}
                                            <PwaInstallButton/>
                                        </MInline>
                                        <MText tone={"muted"}>
                                            {t("login.footer_by")}{" "}
                                            <MLink
                                                href={"https://banzamel.pl"}
                                                target={"_blank"}
                                                rel={"noopener noreferrer"}
                                                tone={"accent"}
                                            >
                                                banzamel
                                            </MLink>
                                        </MText>
                                    </MInline>
                                </MCardFooter>
                            </MCard>
                        </MReveal>
                    </MGridItem>

                    <MGridItem>
                        <MReveal direction={"left"} distance={28} trigger={"mount"}>
                            <MCard>
                                <MCardBody>
                                    <MStack spacing={"lg"}>
                                        <MInline justify={"start"}>
                                            <MImage
                                                src={"/vision-logo.png"}
                                                alt={t("login.brand_alt")}
                                                height={108}
                                            />
                                        </MInline>

                                        <MGrid type={"row"}>
                                            <MGridItem sm={4}>
                                                <MDashboardIllustration/>
                                            </MGridItem>
                                            <MGridItem>
                                                <MStack spacing={"sm"}>
                                                    <MText tone={"muted"} size={"lg"}>
                                                        {t("login.brand_text")}
                                                    </MText>
                                                </MStack>
                                            </MGridItem>
                                        </MGrid>
                                    </MStack>
                                </MCardBody>
                                <MCardFooter>
                                    <MInline wrap={"wrap"}>
                                        <MBadge color={"primary"}>{t("login.badge_pwa")}</MBadge>
                                        <MBadge color={"neutral"}>
                                            {t("login.badge_manager")}
                                        </MBadge>
                                    </MInline>
                                </MCardFooter>
                            </MCard>
                        </MReveal>
                    </MGridItem>
                </MGrid>
            </MContainer>
        </MSection>
    );
}
