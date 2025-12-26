<table width="100%" style="background: #fafbfc; border-top: 1px solid #e2e8f0;">
  <tr>
    <td align="center" style="font-size: 13px; color:#888; padding:18px 0 0 0;">
        <div>
            &copy; {{ date('Y') }} {{ get_option('contact_company_name', 'Your Company Name') }}.
            </div>
            <div style="font-size:12px; margin-top:2px;">
            <a href="{{ get_option('contact_company_website', '#') }}"
                style="color:#888; text-decoration:underline;" target="_blank">
                {{ get_option('contact_company_website', 'www.yourcompany.com') }}
            </a>
        </div>
    </td>
  </tr>
</table>
