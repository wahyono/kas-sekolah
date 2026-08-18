import { Injectable, Logger } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import * as nodemailer from 'nodemailer';

@Injectable()
export class NotificationService {
  private readonly logger = new Logger(NotificationService.name);
  private transporter: nodemailer.Transporter | null = null;

  constructor(private configService: ConfigService) {
    this.initTransporter();
  }

  private initTransporter() {
    const host = this.configService.get<string>('SMTP_HOST') || 'smtp.gmail.com';
    const port = Number(this.configService.get<number>('SMTP_PORT')) || 587;
    const user = this.configService.get<string>('SMTP_USER');
    const pass = this.configService.get<string>('SMTP_PASS'); // 16-character Google App Password

    if (user && pass) {
      this.transporter = nodemailer.createTransport({
        host,
        port,
        secure: port === 465,
        auth: {
          user,
          pass,
        },
      });
      this.logger.log(`📧 Google App Password SMTP Transporter initialized for: ${user}`);
    } else {
      this.logger.warn(`⚠️ Google SMTP App Password not configured in .env (SMTP_USER/SMTP_PASS missing). Email dispatch will be simulated in console.`);
    }
  }

  async sendBillingNotificationEmail(studentEmail: string, studentName: string, schemeTitle: string, amountDue: number, dueDate: Date) {
    this.logger.log(`📧 [EMAIL DISPATCH] To: ${studentEmail}`);
    
    if (this.transporter) {
      try {
        const fromUser = this.configService.get<string>('SMTP_FROM') || this.configService.get<string>('SMTP_USER');
        await this.transporter.sendMail({
          from: `"Kas Sekolah" <${fromUser}>`,
          to: studentEmail,
          subject: `[Kas Sekolah] Tagihan Iuran Kas Baru - ${schemeTitle}`,
          html: `<div style="font-family: sans-serif; padding: 20px;">
            <h2>Notifikasi Tagihan Iuran Kas Sekolah</h2>
            <p>Halo <b>${studentName}</b>,</p>
            <p>Anda memiliki tagihan iuran baru:</p>
            <ul>
              <li><b>Judul Tagihan:</b> ${schemeTitle}</li>
              <li><b>Jumlah:</b> Rp ${amountDue.toLocaleString('id-ID')}</li>
              <li><b>Batas Pembayaran:</b> ${dueDate.toISOString().substring(0, 10)}</li>
            </ul>
            <p>Silakan melakukan pembayaran melalui Korlas atau bendahara sekolah.</p>
          </div>`,
        });
        this.logger.log(`✅ Real email successfully sent to ${studentEmail}`);
        return { success: true, recipient: studentEmail, dispatchedAt: new Date().toISOString() };
      } catch (err: any) {
        this.logger.error(`❌ Failed to send email to ${studentEmail}: ${err.message}`);
        return { success: false, error: err.message };
      }
    }

    return { success: true, simulated: true, recipient: studentEmail, dispatchedAt: new Date().toISOString() };
  }

  async sendReportArchiveEmail(recipientEmail: string, reportTitle: string, pdfBufferInfo: string) {
    this.logger.log(`📧 [EMAIL DISPATCH ARCHIVE PDF] To: ${recipientEmail}`);

    if (this.transporter) {
      try {
        const fromUser = this.configService.get<string>('SMTP_FROM') || this.configService.get<string>('SMTP_USER');
        
        const attachments = [];
        if (pdfBufferInfo) {
          let base64Content = pdfBufferInfo;
          if (pdfBufferInfo.includes(';base64,')) {
            base64Content = pdfBufferInfo.split(';base64,')[1];
          }
          if (base64Content !== 'PDF Buffer Encoded' && base64Content !== 'PDF Buffer') {
            attachments.push({
              filename: `${reportTitle.replace(/\s+/g, '_')}.pdf`,
              content: Buffer.from(base64Content, 'base64'),
              contentType: 'application/pdf'
            });
          }
        }

        await this.transporter.sendMail({
          from: `"Kas Sekolah" <${fromUser}>`,
          to: recipientEmail,
          subject: `[Kas Sekolah] Arsip Laporan Keuangan PDF - ${reportTitle}`,
          html: `<div style="font-family: sans-serif; padding: 20px; line-height: 1.6;">
            <h2 style="color: #4f46e5;">Arsip Laporan Keuangan Kas Sekolah</h2>
            <p>Halo,</p>
            <p>Terlampir dokumen laporan keuangan resmi dalam format PDF dengan judul: <b>${reportTitle}</b>.</p>
            <p>Laporan ini dikirim secara otomatis melalui sistem manajemen Kas Sekolah terpadu.</p>
            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;" />
            <p style="font-size: 11px; color: #64748b;">Pesan ini dikirim secara otomatis, mohon tidak membalas email ini.</p>
          </div>`,
          attachments
        });
        this.logger.log(`✅ Real report email with attachment successfully sent to ${recipientEmail}`);
        return { success: true, recipient: recipientEmail, reportTitle, dispatchedAt: new Date().toISOString() };
      } catch (err: any) {
        this.logger.error(`❌ Failed to send report email to ${recipientEmail}: ${err.message}`);
        return { success: false, error: err.message };
      }
    }

    return { success: true, simulated: true, recipient: recipientEmail, reportTitle, dispatchedAt: new Date().toISOString() };
  }
}

