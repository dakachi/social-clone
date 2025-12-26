<table width="100%" style="background:#f4f4f4; color:#888; padding:24px 0 0 0;">
  <tr>
    <td align="center" style="font-size: 13px;">
      <div style="margin-bottom:8px;">&copy; {{ date('Y') }} {{ get_option('contact_company_name', 'Your Company Name') }}. All rights reserved.</div>
      <div style="font-size:12px;">{{ get_option('contact_location', 'Your Company Address') }}</div>
      <div style="margin-top:8px;">
        <a href="{{ get_option('social_page_facebook', '#') }}" style="color:#248bcb; text-decoration:none;">Facebook</a> &middot;
        <a href="{{ get_option('social_page_x', '#') }}" style="color:#248bcb; text-decoration:none;">X (Twitter)</a>
      </div>
    </td>
  </tr>
</table>
