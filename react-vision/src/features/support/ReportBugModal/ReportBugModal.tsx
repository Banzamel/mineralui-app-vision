import {useState} from 'react'

import {MButton} from '@banzamel/mineralui-pro/controls'
import {useMToast} from '@banzamel/mineralui-pro/feedback'
import {useMI18n} from '@banzamel/mineralui-pro/i18n'
import {MTextarea} from '@banzamel/mineralui-pro/inputs'
import {MInline, MStack} from '@banzamel/mineralui-pro/layout'
import {MModal} from '@banzamel/mineralui-pro/overlays'

import pl from '../../../i18n/pl.json'

interface ReportBugModalProps {
    open: boolean
    onClose: () => void
}

export function ReportBugModal({open, onClose}: ReportBugModalProps) {
    const {t} = useMI18n<typeof pl>()
    const {toast} = useMToast()
    const [message, setMessage] = useState('')

    function handleClose() {
        setMessage('')
        onClose()
    }

    function handleSubmit() {
        toast({
            title: t('report_bug_modal.sent_title'),
            message: t('report_bug_modal.sent_message'),
            color: 'success',
        })
        handleClose()
    }

    return (
        <MModal
            open={open}
            onClose={handleClose}
            title={t('report_bug_modal.title')}
            size={'md'}
            footer={
                <MInline justify={'between'} wrap={'wrap'}>
                    <MButton variant={'ghost'} onClick={handleClose}>
                        {t('report_bug_modal.cancel')}
                    </MButton>
                    <MButton
                        variant={'filled'}
                        color={'primary'}
                        onClick={handleSubmit}
                        disabled={message.trim().length === 0}
                    >
                        {t('report_bug_modal.submit')}
                    </MButton>
                </MInline>
            }
        >
            <MStack spacing={'md'}>
                <MTextarea
                    label={t('report_bug_modal.message_label')}
                    placeholder={t('report_bug_modal.message_placeholder')}
                    value={message}
                    onChange={(event) => setMessage(event.target.value)}
                    minRows={4}
                    maxRows={10}
                    autoResize
                    fullWidth
                />
            </MStack>
        </MModal>
    )
}
