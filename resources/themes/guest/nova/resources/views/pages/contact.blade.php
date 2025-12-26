<section class="pt-24 pb-36 bg-blueGray-50 overflow-hidden">
  <div class="container px-4 mx-auto">
    <div class="mb-20 text-center md:max-w-xl mx-auto">
      <h2 class="mb-4 text-6xl md:text-7xl font-bold font-heading tracking-px-n leading-tight">Our Contacts</h2>
      <p class="text-lg text-gray-600 font-medium leading-normal md:max-w-lg mx-auto">
        {{ get_option('contact_company_name', 'Your Company Name') }}
      </p>
    </div>
    <div class="flex flex-wrap xl:items-center -m-8">
      <div class="w-full md:w-1/2 p-8">
        <div class="max-w-max mx-auto overflow-hidden rounded-3xl">
          <img class="transform hover:scale-105 transition ease-in-out duration-1000" src="{{ theme_public_asset('images/contact/man.png') }}" alt=""/>
        </div>
      </div>
      <div class="w-full md:w-1/2 p-8">
        <p class="mb-4 text-sm font-bold uppercase tracking-px">Website</p>
        <ul class="mb-8">
          <li class="text-lg text-gray-600 font-medium leading-normal">
            <a href="{{ get_option('contact_company_website', '#') }}" class="text-indigo-700 hover:underline" target="_blank">
              {{ get_option('contact_company_website', 'https://yourcompany.com') }}
            </a>
          </li>
        </ul>
        <p class="mb-4 text-sm font-bold uppercase tracking-px">Email</p>
        <ul class="mb-8">
          <li class="text-lg text-gray-600 font-medium leading-normal">
            {{ get_option('contact_email', 'support@yourcompany.com') }}
          </li>
        </ul>
        <p class="mb-4 text-sm font-bold uppercase tracking-px">Phone</p>
        <ul class="mb-8">
          <li class="text-lg text-gray-600 font-medium leading-normal">
            {{ get_option('contact_phone_number', '+1 234 567 890') }}
          </li>
        </ul>
        <p class="mb-4 text-sm font-bold uppercase tracking-px">Working Hours</p>
        <ul class="mb-8">
          <li class="text-lg text-gray-600 font-medium leading-normal">
            {{ get_option('contact_working_hours', 'Mon - Fri: 09:00 AM - 06:00 PM') }}
          </li>
        </ul>
        <p class="mb-4 text-sm font-bold uppercase tracking-px">Address</p>
        <ul>
          <li class="text-lg text-gray-600 font-medium leading-normal">
            {{ get_option('contact_location', '123 Main Street, City, Country') }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</section>
