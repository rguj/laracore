
  
<div>
  <u></u>
  <div style="width:100%!important;min-width:100%;color:#0a0836;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:14px;line-height:1.5;margin:0;padding:0" bgcolor="#f6fafb">
    <center style="width:100%;min-width:580px">
      <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" style="table-layout:fixed;border-spacing:0;border-collapse:collapse;width:100%!important;min-width:100%;color:#0a0836;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen,Ubuntu,Cantarell,Fira Sans,Droid Sans,Helvetica Neue,sans-serif;font-size:14px;line-height:1.5;margin:0;padding:0" bgcolor="#f6fafb">
        <tbody><tr style="padding:0">
          <td align="center" valign="top" style="word-break:break-word;border-collapse:collapse!important;padding:10px 10px 0">
            {{-- <a rel="noopener noreferrer" href="{{ $data['from']['url'] }}" style="color:#00b08c!important" target="_blank">
              <img alt="{{ $data['from']['name'] }}" title="{{ $data['from']['name'] }}" style="width:130px!important;max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:38px!important;border:none" src="https://mail.google.com/mail/u/0?ui=2&amp;ik=b4965500c3&amp;attid=0.1&amp;permmsgid=msg-f:1688736585225714218&amp;th=176f98fa1995ba2a&amp;view=fimg&amp;sz=s0-l75-ft&amp;attbid=ANGjdJ_5nvoVF4WKJapqM8l_OdKQbUl1WyFMAjeLAK9WqDqLmkjZ7qFulNJsKyL02szLbkfOlC15aH8dSMsWwoxh6PkODn0dp2SJw31A8NTs2pbWpKbqvZpuVj0HbGg&amp;disp=emb">
            </a> --}}
			<a rel="noopener noreferrer" href="{{ $data['from']['url'] }}" style="color:#00b08c!important" target="_blank">
				{{-- <img alt="{{ $data['from']['name'] }}" title="{{ $data['from']['name'] }}" style="max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:38px!important;border:none" src="{{ asset('assets/media/misc/oes-small.png') }}"> --}}
				<img title="{{ $data['from']['name'] }}" style="max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:38px!important;border:none" src="{{ asset('assets/media/misc/oes-small.png') }}">
            </a>
          </td>
         </tr>
        <tr style="padding:0">
          <td align="center" valign="top" style="word-break:break-word;border-collapse:collapse!important;width:100%!important;min-width:100%;margin:0;padding:20px 10px 30px">

            <table border="0" cellpadding="0" cellspacing="0" width="580" style="table-layout:auto;border-spacing:0;border-collapse:collapse;border-radius:10px;padding:0" bgcolor="#fff">
              
              <tbody><tr style="padding:0">
                <td align="left" valign="top" class="m_3525988403364873053content" style="word-break:break-word;border-collapse:collapse!important;padding:30px 40px">

  <table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:auto;border-spacing:0;border-collapse:collapse;padding:0">
    <tbody><tr style="padding:0">
      <td align="left" valign="middle" style="word-break:break-word;border-collapse:collapse!important;padding:0">

        
        <h1 style="word-break:normal;line-height:21px;font-size:18px;font-weight:700;padding-bottom:10px;margin:0">Hello <a href="mailto:{{ $data['to']['email'] }}" style="color:#006ab0!important" target="_blank">{{ $data['to']['email'] }}</a></h1>

        <p style="font-size:14px;padding-bottom:10px;margin:0">You have requested for password reset in <span class="il">{{ $data['from']['name'] }}</span>.</p>
        <p style="font-size:14px;padding-bottom:10px;margin:0">Please reset your password by clicking the button below.</p>
      </td>
    </tr>


    <tr style="padding:0">
      <td align="center" valign="middle" style="word-break:break-word;border-collapse:collapse!important;padding:25px 0px 0px">
        <table border="0" cellpadding="0" cellspacing="0" width="335" class="m_3525988403364873053button-block" style="table-layout:auto;border-spacing:0;border-collapse:separate;width:auto;padding:0">
          <tbody><tr style="padding:0">
            <td align="center" valign="middle" class="m_3525988403364873053button" style="word-break:break-word;border-collapse:collapse!important;border-radius:25px;padding:10px 25px" bgcolor="#006ab0">
              <a href="{{ $data['to']['link_activate'] }}" style="color:#fff!important;display:block;font-size:14px;font-weight:700;text-decoration:none" target="_blank">Reset my password</a>
            </td>
          </tr>
        </tbody></table>
      </td>
    </tr>

    <tr style="padding:0">
      <td align="left" valign="middle" style="word-break:break-word;border-collapse:collapse!important;padding: 30px 0px 0px 0px;">
        <p style="font-size:14px;padding-bottom:10px;margin:0">Or, enter this code on the <a style="color:#006ab0!important" href="{{ $data['to']['link_manual'] }}">Password Reset Page</a>.<br></p>
      </td>
    </tr>

    <tr style="padding:0">
      <td align="center" valign="middle" style="word-break:break-word;border-collapse:collapse!important; padding: 35px 0px 0px 0px;">
        <table border="0" cellpadding="0" cellspacing="0" class="m_3525988403364873053button-block" style="table-layout:auto;border-spacing:0;border-collapse:separate;width:100%;padding:0">
          <tbody><tr style="padding:0">
            <td align="center" valign="middle" class="m_3525988403364873053button" style="word-break:break-word;border-collapse:collapse!important;border-radius:5px;padding:10px 25px;background-color: #efefef;
            border: 1px solid #dedede;" bgcolor="#00b08c">
              {{ $data['to']['code'] }}
            </td>
          </tr>
        </tbody></table>
      </td>
    </tr>


  <tr style="padding:0">
      <td align="left" valign="middle" style="word-break:break-word;border-collapse:collapse!important;padding:0">
        {{-- <p style="font-size:14px;;margin:0; padding: 35px 0px 0px 0px;">Please note that unverified accounts are automatically deleted in 30 days after sign up.</p> --}}
        <br>
        <p style="font-size:14px;padding-bottom:10px;margin:0">This code will expire after {{ $data['expiry'] }} minutes.</p>
        <p style="font-size:14px;padding-bottom:10px;margin:0; color:black;">If you didn't request this, please ignore this email.</p>

      </td>
    </tr>
  </tbody></table>


                  </td>
                </tr>

                
                <tr style="padding:0">
                  <td align="left" valign="middle" class="m_3525988403364873053inner-footer" style="word-break:break-word;border-collapse:collapse!important;padding:0 40px 30px">

                    
                    <table border="0" cellpadding="0" cellspacing="0" width="50%" style="table-layout:auto;border-spacing:0;border-collapse:collapse;padding:0">
                      <tbody><tr style="padding:0">
                        <td align="left" valign="middle" style="word-break:break-word;border-collapse:collapse!important;border-top-width:1px;border-top-color:#e4e4e9;border-top-style:solid;font-size:12px;line-height:1.5;padding:20px 0 0">
                          <strong>Yours, {{ $data['from']['name'] }}</span> Team</strong><br>
                          <span class="il"><br>
                          <span class="il">PIT - Online Enrollment System</span><br>
                          {{-- <a href="mailto:information@pit.edu.ph" rel="noopener noreferrer" style="color:#00b08c!important" target="_blank">information@pit.edu.ph</a><br>
                          <span class="il">(053) 555-0000</span><br> --}}
                        </td>
                      </tr>
                    </tbody></table>

                  </td>
                </tr>

              </tbody></table>

            </td>
          </tr>
          <tr><td><br>
          </td></tr><tr style="padding:0">
            <td align="center" valign="top" style="word-break:break-word;border-collapse:collapse!important;padding:0 10px 10px">
              <table border="0" cellpadding="0" cellspacing="0" width="580" style="table-layout:auto;border-spacing:0;border-collapse:collapse;padding:0">
                <tbody><tr style="padding:0">
                  <td align="center" valign="top" class="m_3525988403364873053footer" style="word-break:break-word;border-collapse:collapse!important;font-size:11px;color:#8d8c9f;padding:0">
                    
                    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="m_3525988403364873053footer" style="table-layout:auto;border-spacing:0;border-collapse:collapse;font-size:11px;color:#8d8c9f;padding:0">
                      <tbody><tr style="padding:0">
                      <td align="left" valign="middle" class="m_3525988403364873053footer-section" style="word-break:break-word;border-collapse:collapse!important;padding:0">
                          Palompon Institute of Technology<br>
                          Evangelista St., Brgy. Guiwan 2, Palompon, Leyte<br>
                          6538, Philippines
                        </td>
                        <td align="right" valign="middle" class="m_3525988403364873053footer-section" style="word-break:break-word;border-collapse:collapse!important;padding:0">
                          <table border="0" cellpadding="0" cellspacing="0" class="m_3525988403364873053social-block" style="table-layout:auto;border-spacing:0;border-collapse:collapse;padding:0">
                            <tbody><tr style="padding:0">
                              <td align="right" valign="middle" class="m_3525988403364873053social-item" style="word-break:break-word;border-collapse:collapse!important;width:60px;padding:0">
                                <a rel="noopener noreferrer" class="m_3525988403364873053social-link" href="javascript:;" style="color:#313a45!important;opacity:.5" target="_blank">
                                  <img alt="Facebook" title="Facebook" src="https://mail.google.com/mail/u/0?ui=2&amp;ik=b4965500c3&amp;attid=0.3&amp;permmsgid=msg-f:1688736585225714218&amp;th=176f98fa1995ba2a&amp;view=fimg&amp;sz=s0-l75-ft&amp;attbid=ANGjdJ8bubGGWnWUu36A1vzDONFsiCee_5lsqi7BgXte8COOkKXEcCjbqjx4gc8ejJBfcD61nEYw7ydWBOzPN-PLKPDMVQe8hoLK5aH7aO4h3QDIKnxRBZ3h_AM9ixs&amp;disp=emb" style="width:34px!important;max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:34px!important;border:none" data-image-whitelisted="" class="CToWUd">
  </a>                            </td>{{--
                              <td align="right" valign="middle" class="m_3525988403364873053social-item" style="word-break:break-word;border-collapse:collapse!important;width:60px;padding:0">
                                <a rel="noopener noreferrer" class="m_3525988403364873053social-link" href="" style="color:#313a45!important;opacity:.5" target="_blank" >
                                  <img alt="Twitter" title="Twitter" src="https://mail.google.com/mail/u/0?ui=2&amp;ik=b4965500c3&amp;attid=0.2&amp;permmsgid=msg-f:1688736585225714218&amp;th=176f98fa1995ba2a&amp;view=fimg&amp;sz=s0-l75-ft&amp;attbid=ANGjdJ_2rmipKZSMCf9TsblfkhoT7sED6hugovc6yRMSSY0GimPx6l44lc5PRtuygil6pDfsAX8G4WqCXnWF8-nFoc0u-6nu1ZZPNP-Sf1Ms8OdYM8vpXYPtkeH4kXg&amp;disp=emb" style="width:34px!important;max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:34px!important;border:none" data-image-whitelisted="" class="CToWUd">
  </a>                            </td>
                              <td align="right" valign="middle" class="m_3525988403364873053social-item" style="word-break:break-word;border-collapse:collapse!important;width:60px;padding:0">
                                <a rel="noopener noreferrer" class="m_3525988403364873053social-link" href="" style="color:#313a45!important;opacity:.5" target="_blank" >
                                  <img alt="LinkedIn" title="LinkedIn" src="https://mail.google.com/mail/u/0?ui=2&amp;ik=b4965500c3&amp;attid=0.4&amp;permmsgid=msg-f:1688736585225714218&amp;th=176f98fa1995ba2a&amp;view=fimg&amp;sz=s0-l75-ft&amp;attbid=ANGjdJ-Jip3-_Rzj1t6JunZ1a9I4MYbYvpoO-jv39RYTOZMcdCo4LSth9coEJ0ndNVA7heP4uB0g-mVKmQXt4qTi3os5lhfX1MQgtuqK-u8ioNBgxEddjdGVfw2Z6PQ&amp;disp=emb" style="width:34px!important;max-width:100%;outline:none;text-decoration:none;vertical-align:middle;height:34px!important;border:none" data-image-whitelisted="" class="CToWUd">
  </a>                            </td>--}}
                            </tr>
                          </tbody></table>
                        </td>
                      </tr>
                    </tbody></table>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
        </tbody></table>
      </center>
    <img src="https://ci6.googleusercontent.com/proxy/K470dVE7AidkhwdTMO7UgbxF0SkTSTodJPyhHxja85n05WAmPOkkvgu3G98aRvn8h2YMV-pjcb504jur8zWEBo5Paj_z8pS9AXB_uCD_NhQlA0rH63fR9Yu4zCtrj5kU1BuXAj1XSKyOzBnnb1Hct74NUh88cTReDN6-EnHaP6Ct7jNgO2eVU9U5A-YUqqsFGXvH8jHjJtiXxZa7usBSixD32IrGUkPlDDrZy5pT4JJpnpUd5IaFWFZPJB8BPCzLD1SkSz6ZRo-oz01G5Vvhs7wOGtuzS-XpZkUmH5AO21oJymh3u6M2vHvZq14mXojOgPWz2wS7J6o0WsspZQcIu_1KZHaMArz6z004N-sWAz31pKhVecQpvYUhtWCO6LXgpJWSqwTYXQ5HNS-EeDG0hg_0XCx8Y453GocnST3jWylQmTAfahuUsGswv-yXSdB88823o1CKOyJBwJ5PorPbwl2X3rL6_nYwmBMIRBdihDJ5JfI06uskug8sfltsHhw=s0-d-e1-ft#https://u8208408.ct.sendgrid.net/wf/open?upn=2v4Me3xJo-2FQZ-2F0ZeBFkCtcWrMO8lPl7jhw8VecVH48XQHVgECe-2FadegOT9xJDs-2F2mxk1LKPbCov-2F2lpu5ghRTdkOB33bTgdaECt6k1HJ-2FmRN7A9qET9R4T0vqMnAMvbuqCr47FGQLQ-2Fg4Iht9NGtT89P5FZ9xF0MpOlG8yFxQskcB22fKvtHMEBtU72YFMJ3iiTDqRiqneXXxA8ilwqhCIdfcg67giWQClAoJSYvQ9YomE6Hzl6adLrQ4yIPdPY31rMEU3yEtyr8ypYIglriRQmJEQl5o1-2Bf1xLkZRfAg68-3D" alt="" width="1" height="1" border="0" style="height:1px!important;width:1px!important;border-width:0!important;margin-top:0!important;margin-bottom:0!important;margin-right:0!important;margin-left:0!important;padding-top:0!important;padding-bottom:0!important;padding-right:0!important;padding-left:0!important" class="CToWUd"></div><div class="yj6qo"></div><div class="adL">

  </div>
</div>
